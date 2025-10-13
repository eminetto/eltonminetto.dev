---
title: "Testando APIs em Golang usando apitest"
subtitle: ""
date: "2020-04-10T08:33:24+02:00"
bigimg: ""
tags:
  - go
---

Uma das grandes vantagens da linguagem Go é sua biblioteca padrão, que contém muitas das funcionalidades que são úteis no desenvolvimento de aplicações modernas, como servidor e cliente HTTP, parser de JSON, e testes. É exatamente sobre esse último ponto que vou falar neste post.

Com a biblioteca padrão é possível escrever testes para sua API, como no exemplo a seguir.

## Código da API

No nosso arquivo `main.go` vamos criar uma API simples:

```go
package main

import (
   "encoding/json"
   "log"
   "net/http"
   "os"
   "strconv"
   "time"

   "github.com/codegangsta/negroni"
   "github.com/gorilla/context"
   "github.com/gorilla/mux"
)

//Bookmark data
type Bookmark struct {
   ID   int    `json:"id"`
   Link string `json:"link"`
}

func main() {
   //router
   r := mux.NewRouter()
   //midllewares
   n := negroni.New(
      negroni.NewLogger(),
   )
   //routes
   r.Handle("/v1/bookmark", n.With(
      negroni.Wrap(bookmarkIndex()),
   )).Methods("GET", "OPTIONS").Name("bookmarkIndex")
   r.Handle("/v1/bookmark/{id}", n.With(
      negroni.Wrap(bookmarkFind()),
   )).Methods("GET", "OPTIONS").Name("bookmarkFind")
   http.Handle("/", r)
   //server
   logger := log.New(os.Stderr, "logger: ", log.Lshortfile)
   srv := &http.Server{
      ReadTimeout:  5 * time.Second,
      WriteTimeout: 10 * time.Second,
      Addr:         ":8080",
      Handler:      context.ClearHandler(http.DefaultServeMux),
      ErrorLog:     logger,
   }
   //start server
   err := srv.ListenAndServe()
   if err != nil {
      log.Fatal(err.Error())
   }
}

func bookmarkIndex() http.Handler {
   return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
      data := []*Bookmark{
         {
            ID:   1,
            Link: "http://google.com",
         },
         {
            ID:   2,
            Link: "https://apitest.dev",
         },
      }
      w.Header().Set("Content-Type", "application/json")
      if err := json.NewEncoder(w).Encode(data); err != nil {
         w.WriteHeader(http.StatusInternalServerError)
         w.Write([]byte("Error reading bookmarks"))
      }
   })
}

func bookmarkFind() http.Handler {
   return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
      vars := mux.Vars(r)
      id, err := strconv.Atoi(vars["id"])
      if err != nil {
         w.WriteHeader(http.StatusInternalServerError)
         w.Write([]byte("Error reading parameters"))
         return
      }
      data := []*Bookmark{
         {
            ID:   2,
            Link: "https://apitest.dev",
         },
      }
      if id != data[0].ID {
         w.WriteHeader(http.StatusNotFound)
         w.Write([]byte("Not found"))
         return
      }
      w.Header().Set("Content-Type", "application/json")
      if err := json.NewEncoder(w).Encode(data[0]); err != nil {
         w.WriteHeader(http.StatusInternalServerError)
         w.Write([]byte("Error reading bookmark"))
      }
   })
}
```

## Compilando

Antes de compilar nossa API precisamos iniciar nosso projeto como um módulo, para que as dependências externas sejam instaladas, como o _negroni_ e o _gorilla_. Para isso executamos o comando:

```
go mod init github.com/eminetto/post-apitest
go: creating new go.mod: module github.com/eminetto/post-apitest
```

Vai ser criado um arquivo chamado `go.mod` que contém a lista de dependências do nosso projeto. Ao executar a compilação, elas serão instaladas:

```
go build
go: finding module for package github.com/gorilla/context
go: finding module for package github.com/gorilla/mux
go: finding module for package github.com/codegangsta/negroni
go: found github.com/codegangsta/negroni in github.com/codegangsta/negroni v1.0.0
go: found github.com/gorilla/context in github.com/gorilla/context v1.1.1
go: found github.com/gorilla/mux in github.com/gorilla/mux v1.7.4
```

## Testes com a biblioteca padrão

Vamos agora criar os testes para esta API. Nosso arquivo `main_test.go` ficou desta forma:

```go
package main

import (
   "net/http"
   "net/http/httptest"
   "testing"

   "github.com/gorilla/mux"
)

func Test_bookmarkIndex(t *testing.T) {
   r := mux.NewRouter()
   r.Handle("/v1/bookmark", bookmarkIndex())
   ts := httptest.NewServer(r)
   defer ts.Close()
   res, err := http.Get(ts.URL + "/v1/bookmark")
   if err != nil {
      t.Errorf("Expected nil, received %s", err.Error())
   }
   if res.StatusCode != http.StatusOK {
      t.Errorf("Expected %d, received %d", http.StatusOK, res.StatusCode)
   }
}

func Test_bookmarkFind(t *testing.T) {
   r := mux.NewRouter()
   r.Handle("/v1/bookmark/{id}", bookmarkFind())
   ts := httptest.NewServer(r)
   defer ts.Close()
   t.Run("not found", func(t *testing.T) {
      res, err := http.Get(ts.URL + "/v1/bookmark/1")
      if err != nil {
         t.Errorf("Expected nil, received %s", err.Error())
      }
      if res.StatusCode != http.StatusNotFound {
         t.Errorf("Expected %d, received %d", http.StatusNotFound, res.StatusCode)
      }
   })
   t.Run("found", func(t *testing.T) {
      res, err := http.Get(ts.URL + "/v1/bookmark/2")
      if err != nil {
         t.Errorf("Expected nil, received %s", err.Error())
      }
      if res.StatusCode != http.StatusOK {
         t.Errorf("Expected %d, received %d", http.StatusOK, res.StatusCode)
      }
   })
}
```

Executando os testes vemos que todos estão passando com sucesso:

```
go test -v
=== RUN   Test_bookmarkIndex
--- PASS: Test_bookmarkIndex (0.00s)
=== RUN   Test_bookmarkFind
=== RUN   Test_bookmarkFind/not_found
=== RUN   Test_bookmarkFind/found
--- PASS: Test_bookmarkFind (0.00s)
    --- PASS: Test_bookmarkFind/not_found (0.00s)
    --- PASS: Test_bookmarkFind/found (0.00s)
PASS
ok  	github.com/eminetto/post-apitest	0.371s
```

Desta forma testamos nossa API usando apenas a biblioteca padrão da linguagem, o que é algo bem interessante. Mas o código dos testes não são tão legíveis, principalmente quando estivermos testando uma API grande, com diversos _endpoints_.

## Usando o apitest

Para melhorar o código do nosso teste podemos usar algumas bibliotecas de terceiros, como a [apitest](https://apitest.dev), que simplifica bastante o processo.

Vamos iniciar instalando os pacotes necessários. No terminal executamos:

```
go get github.com/steinfletcher/apitest
go: github.com/steinfletcher/apitest upgrade => v1.4.5
```

e

```
go get github.com/steinfletcher/apitest-jsonpath
go: github.com/steinfletcher/apitest-jsonpath upgrade => v1.5.0
```

Vamos agora refatorar o arquivo `main_test.go`:

```go
package main

import (
   "net/http"
   "net/http/httptest"
   "testing"

   "github.com/gorilla/mux"
   "github.com/steinfletcher/apitest"
   jsonpath "github.com/steinfletcher/apitest-jsonpath"
)

func Test_bookmarkIndex(t *testing.T) {
   r := mux.NewRouter()
   r.Handle("/v1/bookmark", bookmarkIndex())
   ts := httptest.NewServer(r)
   defer ts.Close()
   apitest.New().
      Handler(r).
      Get("/v1/bookmark").
      Expect(t).
      Status(http.StatusOK).
      End()
}

func Test_bookmarkFind(t *testing.T) {
   r := mux.NewRouter()
   r.Handle("/v1/bookmark/{id}", bookmarkFind())
   ts := httptest.NewServer(r)
   defer ts.Close()
   t.Run("not found", func(t *testing.T) {
      apitest.New().
         Handler(r).
         Get("/v1/bookmark/1").
         Expect(t).
         Status(http.StatusNotFound).
         End()
   })
   t.Run("found", func(t *testing.T) {
      apitest.New().
         Handler(r).
         Get("/v1/bookmark/2").
         Expect(t).
         Assert(jsonpath.Equal(`$.link`, "https://apitest.dev")).
         Status(http.StatusOK).
         End()
   })
}
```

Os testes ficaram bem mais legíveis e ganhamos a funcionalidade de testar o JSON resultante. Uma observação: também é possível testar o JSON resultante usando apenas a biblioteca padrão, mas são necessárias algumas linhas a mais no teste.

Na [documentação](https://apitest.dev) é possível ver como a biblioteca é poderosa, permitindo configurações avançadas de _headers_, _cookies_, _debug_ e _mocks_. Vale a pena dedicar um tempo estudando as opções e vendo os exemplos fornecidos.

## Gerando relatórios

Uma funcionalidade interessante que gostaria de mostrar neste post é a geração de relatórios. Basta uma pequena alteração no código, a inclusão da linha `Report(apitest.SequenceDiagram()).` nos testes, como no exemplo:

```go
apitest.New().
   Report(apitest.SequenceDiagram()).
   Handler(r).
   Get("/v1/bookmark").
   Expect(t).
   Status(http.StatusOK).
   End()
```

E ao executarmos novamente os testes temos o seguinte resultado:

```
go test -v
=== RUN   Test_bookmarkIndex
Created sequence diagram (3157381659_2166136261.html): /Users/eminetto/Projects/post-apitest/.sequence/3157381659_2166136261.html
--- PASS: Test_bookmarkIndex (0.00s)
=== RUN   Test_bookmarkFind
=== RUN   Test_bookmarkFind/not_found
Created sequence diagram (1543772695_2166136261.html): /Users/eminetto/Projects/post-apitest/.sequence/1543772695_2166136261.html
=== RUN   Test_bookmarkFind/found
Created sequence diagram (1560550314_2166136261.html): /Users/eminetto/Projects/post-apitest/.sequence/1560550314_2166136261.html
--- PASS: Test_bookmarkFind (0.00s)
    --- PASS: Test_bookmarkFind/not_found (0.00s)
    --- PASS: Test_bookmarkFind/found (0.00s)
PASS
ok  	github.com/eminetto/post-apitest	0.296s
```

Abrindo alguns dos relatórios temos o seguinte resultado:

[![apitest1](/images/posts/apitest1.png)](/images/posts/apitest1.png)

[![apitest2](/images/posts/apitest2.png)](/images/posts/apitest2.png)

## Vale a pena usar?

Essa é uma pergunta que não tem uma resposta única. Usando apenas a biblioteca padrão da linguagem o projeto ganha em velocidade de execução dos testes, além de não depender de bibliotecas de terceiros, o que pode ser um problema em algumas equipes.

Ao usar uma biblioteca como o apitest ganha-se em produtividade e facilidade de manutenção, mas perde-se em velocidade de execução. Uma observação quanto a velocidade: executei apenas alguns testes e benchmarks simples, então não posso afirmar com certeza o quanto mais lento ficam os testes em comparação com a biblioteca padrão, mas é visível uma pequena diferença.

Cada time pode fazer seus benchmarks e tomar esta decisão, mas na maioria das vezes acredito que produtividade da equipe vai ganhar vários pontos nesta escolha.
