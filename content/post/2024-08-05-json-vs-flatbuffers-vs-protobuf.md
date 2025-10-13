---
title: JSON vs FlatBuffers vs Protocol Buffers
date: 2024-08-05T21:00:43-03:00
draft: false
tags:
  - go
---
Quando pensamos em comunicação entre serviços/microsserviços a primeira opção que vem na nossa mente é o bom e velho JSON. E isso não é sem razão, pois o formato tem vantanges, como:

- é facilmente legível, tanto por computadores quanto humanos;
- todas as linguagens de programação modernas conseguem ler e gerar JSON;
- é bem menos verboso do que a alternativa anterior, o jurássico XML.

E usar JSON é a recomendação para uma vasta maioria das APIs que são desenvolvidas no dia a dia das empresas. Mas em alguns casos, onde a performance é algo crítico, podemos precisar olhar para outras alternativas. É esse o objetivo deste post, mostrar duas alternativas ao JSON no quesito comunicação entre aplicações.

Mas qual é o problema do JSON? Justamente uma das suas vantagens, o “facilmente legível por humanos” pode ser um ponto fraco em relação a performance. O fato é que precisamos converter o conteúdo do JSON para alguma estrutura que seja conhecida pela linguagem de programação que estamos usando. Uma exceção à essa regra é o caso de estarmos usando JavaScript, pois para ela o JSON é algo nativo. Mas se estiver usando outra linguagem, Go por exemplo, é necessário fazermos um *parse* dos dados, como no exemplo de código (incompleto) a seguir:

```go
type event struct {
	ID      uuid.UUID
	Type    string `json:"type"`
	Source  string `json:"source"`
	Subject string `json:"subject"`
	Time    string `json:"time"`
	Data    string `json:"data"`
}

var e event
err := json.NewDecoder(data).Decode(&e)
if err != nil {
	http.Error(w, err.Error(), http.StatusBadRequest)
}
```

Para resolver este problema podemos testar duas alternativas, o  Protocol Buffers e o Flatbuffers.

## Protocol Buffers

O Protobuf (Protocol Buffers), criado pelo Google é, segundo o [site oficial](https://protobuf.dev/):

> Protocol Buffers são o mecanismo neutro de linguagem, neutro de plataforma e extensível do Google para serializar dados estruturados – pense em XML, mas menor, mais rápido e mais simples. Você define como quer que seus dados sejam estruturados uma vez, então você pode usar código-fonte especial gerado para escrever e ler facilmente seus dados estruturados de e para uma variedade de fluxos de dados e usando uma variedade de linguagens.

Geralmente usado em conjunto com o [gRPC](https://grpc.io/) (mas não necessariamente), o Protobuf é um protocolo binário, o que aumenta bastante a performance se compararmos com o formato texto do JSON. Mas ele “sofre” do mesmo problema do JSON: é preciso que façamos um *parse* para uma estrutura de dados da linguagem que estamos usando. Por exemplo, em Go:

```go
//generated code
type Event struct {
	state         protoimpl.MessageState
	sizeCache     protoimpl.SizeCache
	unknownFields protoimpl.UnknownFields

	Type    string `protobuf:"bytes,1,opt,name=type,proto3" json:"type,omitempty"`
	Subject string `protobuf:"bytes,2,opt,name=subject,proto3" json:"subject,omitempty"`
	Source  string `protobuf:"bytes,3,opt,name=source,proto3" json:"source,omitempty"`
	Time    string `protobuf:"bytes,4,opt,name=time,proto3" json:"time,omitempty"`
	Data    string `protobuf:"bytes,5,opt,name=data,proto3" json:"data,omitempty"`
}

e := Event{}
err := proto.Unmarshal(data, &e)
if err != nil {
	http.Error(w, err.Error(), http.StatusBadRequest)
}
```

Temos um ganho de peformance ao adotarmos um protocolo binário, mas ainda nos resta o problema do *parse* de dados. O nosso terceiro competidor tem como foco resolver este problema.

## Flatbuffers

Segundo o [site oficial](https://flatbuffers.dev/):

> FlatBuffers é uma biblioteca de serialização multiplataforma eficiente para C++, C#, C, Go, Java, Kotlin, JavaScript, Lobster, Lua, TypeScript, PHP, Python, Rust e Swift. Foi originalmente criada no Google para desenvolvimento de jogos e outros aplicativos críticos de desempenho.

Apesar de ter sido criada inicialmente para o desenvolvimento de jogos, ela se encaixa perfeitamente no ambiente que estamos estudando neste post. A sua vantagem é que, além de ser um protocolo binário, não é preciso que façamos o *parse* dos dados. Por exemplo, em Go:

```go
//generated code
e := events.GetRootAsEvent(data, 0)

//we can use the data directly
saveEvent(string(e.Type()), string(e.Source()), string(e.Subject()), string(e.Time()), string(e.Data()))
```

Mas quão mais performático são as duas alternativas ao JSON? Vamos investigar...

## Aplicação

A primeira pergunta que me veio a mente foi “como posso aplicar isso em um cenário real?”. Imaginei o seguinte cenário:

> uma empresa com um aplicativo móvel, acessado diáriamente por milhões de clientes, com uma arquitetura interna de microsserviços e que precisa salvar eventos gerados pelos usuários e sistemas para fins de auditoria. 

Isso é um cenário bem real. Tão real que convivo com ele todos os dias na [empresa](https://picpay.com) onde trabalho :) 

[![events](/images/posts/json_fb_pb_1.png)](/images/posts/json_fb_pb_1.png)

Observação: o cenário acima é uma simplificação e não representa a complexidade real da aplicação que o time mantém. Ela serve para fins didáticos.

O primeiro passo é criarmos a definição de um evento, tanto em Protocol Buffers quanto em Flatbuffers. Ambos definem uma linguagem própria para definição de esquemas, que depois podemos usar para gerar código nas linguagens que iremos usar. Não vou me aprofundar nos detalhes de cada esquema pois isso é facilmente encontrado na documentação. 

O arquivo `event.proto` possui a definição do Protocol Buffer: 

```proto
syntax = "proto3";
package events;

option go_package = "./events_pb";

message Event {
    string type = 1;
    string subject = 2;
    string source = 3;
    string time = 4;
    string data = 5;
}
```

E o arquivo `event.fbs` possui o equivalente em Flatbuffers:


```proto
namespace events;

table Event {
    type: string;
    subject:string;
    source:string;
    time:string;
    data:string;
}

root_type Event;
```

O próximo passo é usar estas definições para gerarmos o código necessário. Os comandos a seguir instalam as dependências no macOS:

```bash
go install google.golang.org/protobuf/cmd/protoc-gen-go@latest
brew install protobuf
protoc -I=. --go_out=./ event.proto
brew install flatbuffers
flatc --go event.fbs
```

O resultado é a criação dos pacotes Go para manipularmos os dados de cada formato. 

Com os requisitos cumpridos, o próximo passo foi a implementação da API de eventos. O `main.go` ficou da seguinte forma:

```go
package main

import (
	"fmt"
	"net/http"
	"os"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
	"github.com/google/uuid"
)

func main() {
	r := handlers()
	http.ListenAndServe(":3000", r)
}

func handlers() *chi.Mux {
	r := chi.NewRouter()
	if os.Getenv("DEBUG") != "false" {
		r.Use(middleware.Logger)
	}
	r.Post("/json", processJSON())
	r.Post("/fb", processFB())
	r.Post("/pb", processPB())
	return r
}

func saveEvent(evType, source, subject, time, data string) {
	if os.Getenv("DEBUG") != "false" {
		id := uuid.New()
		q := fmt.Sprintf("insert into event values('%s', '%s', '%s', '%s', '%s', '%s')", id, evType, source, subject, time, data)
		fmt.Println(q)
	}
	// save event to database
}

```

Para melhor organização criei arquivos para separar cada função, que ficaram da seguinte forma:

```go
package main

import (
	"encoding/json"
	"net/http"

	"github.com/google/uuid"
)

type event struct {
	ID      uuid.UUID
	Type    string `json:"type"`
	Source  string `json:"source"`
	Subject string `json:"subject"`
	Time    string `json:"time"`
	Data    string `json:"data"`
}

func processJSON() http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		var e event
		err := json.NewDecoder(r.Body).Decode(&e)
		if err != nil {
			http.Error(w, err.Error(), http.StatusBadRequest)
		}
		saveEvent(e.Type, e.Source, e.Subject, e.Time, e.Data)
		w.WriteHeader(http.StatusCreated)
		w.Write([]byte("json received"))
	}
}

```

```go
package main

import (
	"io"
	"net/http"

	"github.com/eminetto/post-flatbuffers/events_pb"
	"google.golang.org/protobuf/proto"
)

func processPB() http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		body := r.Body
		data, _ := io.ReadAll(body)

		e := events_pb.Event{}
		err := proto.Unmarshal(data, &e)
		if err != nil {
			http.Error(w, err.Error(), http.StatusBadRequest)
		}
		saveEvent(e.GetType(), e.GetSource(), e.GetSubject(), e.GetTime(), e.GetData())
		w.WriteHeader(http.StatusCreated)
		w.Write([]byte("protobuf received"))
	}
}
```


```go 
package main

import (
	"io"
	"net/http"

	"github.com/eminetto/post-flatbuffers/events"
)

func processFB() http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		body := r.Body
		data, _ := io.ReadAll(body)
		e := events.GetRootAsEvent(data, 0)
		saveEvent(string(e.Type()), string(e.Source()), string(e.Subject()), string(e.Time()), string(e.Data()))
		w.WriteHeader(http.StatusCreated)
		w.Write([]byte("flatbuffer received"))
	}
}

```

Nas funções `processPB()` e `processFB()` podemos ver o uso dos pacotes gerados para manipulação dos dados. 

## Benchmark

O último passo da nossa prova de conceito é a geração do benchmark para compararmos os formatos. Para isso usei o pacote de benchmark da *stdlib* de Go. 

O arquivo `main_test.go` possui os testes de cada formato:

```go
package main

import (
	"bytes"
	"fmt"
	"net/http"
	"net/http/httptest"
	"os"
	"strings"
	"testing"

	"github.com/eminetto/post-flatbuffers/events"
	"github.com/eminetto/post-flatbuffers/events_pb"
	flatbuffers "github.com/google/flatbuffers/go"
	"google.golang.org/protobuf/proto"
)

func benchSetup() {
	os.Setenv("DEBUG", "false")
}

func BenchmarkJSON(b *testing.B) {
	benchSetup()
	r := handlers()
	payload := fmt.Sprintf(`{
		"type": "button.clicked",
		"source": "Login",
		"subject": "user1000",
		"time": "2018-04-05T17:31:00Z",
		"data": "User clicked because X"}`)
	for i := 0; i < b.N; i++ {
		w := httptest.NewRecorder()
		req, _ := http.NewRequest("POST", "/json", strings.NewReader(payload))
		r.ServeHTTP(w, req)
		if w.Code != http.StatusCreated {
			b.Errorf("expected status 201, got %d", w.Code)
		}
	}
}

func BenchmarkFlatBuffers(b *testing.B) {
	benchSetup()
	r := handlers()
	builder := flatbuffers.NewBuilder(1024)
	evtType := builder.CreateString("button.clicked")
	evtSource := builder.CreateString("service-b")
	evtSubject := builder.CreateString("user1000")
	evtTime := builder.CreateString("2018-04-05T17:31:00Z")
	evtData := builder.CreateString("User clicked because X")

	events.EventStart(builder)
	events.EventAddType(builder, evtType)
	events.EventAddSource(builder, evtSource)
	events.EventAddSubject(builder, evtSubject)
	events.EventAddTime(builder, evtTime)
	events.EventAddData(builder, evtData)
	evt := events.EventEnd(builder)
	builder.Finish(evt)

	buff := builder.FinishedBytes()
	for i := 0; i < b.N; i++ {
		w := httptest.NewRecorder()
		req, _ := http.NewRequest("POST", "/fb", bytes.NewReader(buff))
		r.ServeHTTP(w, req)
		if w.Code != http.StatusCreated {
			b.Errorf("expected status 201, got %d", w.Code)
		}
	}
}

func BenchmarkProtobuffer(b *testing.B) {
	benchSetup()
	r := handlers()
	evt := events_pb.Event{
		Type:    "button.clicked",
		Subject: "user1000",
		Source:  "service-b",
		Time:    "2018-04-05T17:31:00Z",
		Data:    "User clicked because X",
	}
	payload, err := proto.Marshal(&evt)
	if err != nil {
		panic(err)
	}
	for i := 0; i < b.N; i++ {
		w := httptest.NewRecorder()
		req, _ := http.NewRequest("POST", "/pb", bytes.NewReader(payload))
		r.ServeHTTP(w, req)
		if w.Code != http.StatusCreated {
			b.Errorf("expected status 201, got %d", w.Code)
		}
	}
}

```

Basicamente o que é feito é a geração de um evento em cada formato e o envio do mesmo para a API. 

Ao executarmos o benchmark temos o seguinte resultado:

```bash
Running tool: /opt/homebrew/bin/go test -benchmem -run=^$ -coverprofile=/var/folders/vn/gff4w90d37xbfc_2tn3616h40000gn/T/vscode-gojAS4GO/go-code-cover -bench . github.com/eminetto/post-flatbuffers/cmd/api -failfast -v

goos: darwin
goarch: arm64
pkg: github.com/eminetto/post-flatbuffers/cmd/api
BenchmarkJSON
BenchmarkJSON-8          	  658386	      1732 ns/op	    2288 B/op	      26 allocs/op
BenchmarkFlatBuffers
BenchmarkFlatBuffers-8   	 1749194	       640.5 ns/op	    1856 B/op	      21 allocs/op
BenchmarkProtobuffer
BenchmarkProtobuffer-8   	 1497356	       696.9 ns/op	    1952 B/op	      21 allocs/op
PASS
coverage: 77.5% of statements
ok  	github.com/eminetto/post-flatbuffers/cmd/api	5.042s
```

Se essa é a primeira vez que você analisa o resultado de um benchmark de Go eu recomendo a leitura [deste post](https://www.practical-go-lessons.com/chap-34-benchmarks#how-to-read-benchmark-results) onde o autor descreve os detalhes de cada coluna e seu significado. 

Para facilitar a visualização eu criei gráficos para as informações mais importantes geradas pelo benchmark:

**‌Número de iterações** (maior é melhor)

[![g1](/images/posts/json_fb_pb_g1.png)](/images/posts/json_fb_pb_g1.png)

**Nanosegundos por operação** (menor é melhor)

[![g2](/images/posts/json_fb_pb_g2.png)](/images/posts/json_fb_pb_g2.png)


**Número de bytes alocados por operação**  (menor é melhor)

[![g3](/images/posts/json_fb_pb_g3.png)](/images/posts/json_fb_pb_g3.png)


**Número de alocações por operação**  (menor é melhor)

[![g4](/images/posts/json_fb_pb_g4.png)](/images/posts/json_fb_pb_g4.png)

## Conclusão

Os números mostram uma grande vantagem dos protocolos binários sobre o JSON, em especial o Flatbuffers. A sua grande vantagem é o fato de não precisarmos fazer *parse* dos dados para estruturas da linguagem que estamos usando. 

Isso significa que você deva refatorar suas aplicações para substitur o JSON por Flatbuffers? Não necessariamente. Performance é apenas um dos fatores que os times devem levar em conta ao selecionar um protocolo de comunicação entre seus serviços e aplicações. Mas se sua aplicação tem bilhões de requests por dia, melhorias de performance como as que foram apresentadas neste post podem fazer uma grande diferença em relação a custos e experiência do usuário. 

Os códigos apresentados aqui encontram-se [neste repositório](https://github.com/eminetto/post-flatbuffers). Eu fiz os exemplos usando a linguagem Go, mas tanto Protocol Buffers quanto Flatbuffers possuem suporte a diversas linguagens de programação, então adoraria ver outras versões destas comparações. Além disso, outros tipos de benchmark podem ser feitos, como consumo de rede, CPU, etc (já que aqui só comparamos memória).  

Espero que este post sirva como apresentação a estes formatos e também como um incentivo a novos testes e experiências. 

