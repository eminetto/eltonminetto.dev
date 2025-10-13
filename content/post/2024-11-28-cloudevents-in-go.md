---
title: Usando CloudEvents em Go
date: 2024-11-28T07:00:43-03:00
draft: false
tags:
  - go
---

Em ambientes complexos é relativamente comum a adoção de uma arquitetura orientada a eventos (Event-driven architecture, ou EDA) para aumentar a escalabilidade e reduzir o acoplamento entre os componentes/serviços. 

Mas ao mesmo tempo que esta abordagem resolve uma série de problemas, um dos desafios enfrentados pelos times é a padronização dos eventos a fim de garantir compatibilidade entre todos os componentes. Para mitigar este desafio podemos fazer uso do projeto [CloudEvents](https://cloudevents.io). 

O projeto tem como objetivo ser uma especificação para a padronização e descrição de eventos trazendo consistência, acessibilidade e portabilidade. Outra vantagem é que além de ser uma [especificação](https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md) o projeto fornece uma série de SDKs para acelerar a adoção entre os times. 

Neste post quero demonstrar o uso do [SDK de Go](https://github.com/cloudevents/sdk-go) (com uma participação especial do [SDK de Python](https://github.com/cloudevents/sdk-python)) para ilustrar o uso em um projeto fictício. 

Vamos considerar um ambiente composto por dois microsserviços, o `user`, que faz a gestão de usuários (CRUD) e um serviço de auditoria, que armazena acontecimentos importantes no ambiente para futura análise.

O código do serviço `user` ficou da seguinte forma:

```go
package main

import (
	"context"
	"encoding/json"
	"log"
	"net/http"
	"time"

	cloudevents "github.com/cloudevents/sdk-go/v2"
	"github.com/cloudevents/sdk-go/v2/protocol"
	"github.com/go-chi/chi/v5"
	"github.com/go-chi/httplog"
	"github.com/google/uuid"
)

const auditService = "http://localhost:8080/"

func main() {
	logger := httplog.NewLogger("user", httplog.Options{
		JSON: true,
	})
	ctx := context.Background()
	ceClient, err := cloudevents.NewClientHTTP()
	if err != nil {
		log.Fatalf("failed to create client, %v", err)
	}

	r := chi.NewRouter()
	r.Use(httplog.RequestLogger(logger))
	r.Post("/v1/user", storeUser(ctx, ceClient))

	http.Handle("/", r)
	srv := &http.Server{
		ReadTimeout:  30 * time.Second,
		WriteTimeout: 30 * time.Second,
		Addr:         ":3000",
		Handler:      http.DefaultServeMux,
	}
	err = srv.ListenAndServe()
	if err != nil {
		logger.Panic().Msg(err.Error())
	}
}

type userRequest struct {
	ID       uuid.UUID
	Name     string `json:"name"`
	Password string `json:"password"`
}

func storeUser(ctx context.Context, ceClient cloudevents.Client) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		oplog := httplog.LogEntry(r.Context())

		var ur userRequest
		err := json.NewDecoder(r.Body).Decode(&ur)
		if err != nil {
			w.WriteHeader(http.StatusBadRequest)
			oplog.Error().Msg(err.Error())
			return
		}
		ur.ID = uuid.New()
		//TODO: store user in a database

		// Create an Event.
		event := cloudevents.NewEvent()
		event.SetSource("github.com/eminetto/post-cloudevents")
		event.SetType("user.storeUser")
		event.SetData(cloudevents.ApplicationJSON, map[string]string{"id": ur.ID.String()})

		// Set a target.
		ctx := cloudevents.ContextWithTarget(context.Background(), auditService)

		// Send that Event.
		var result protocol.Result
		if result = ceClient.Send(ctx, event); cloudevents.IsUndelivered(result) {
			oplog.Error().Msgf("failed to send, %v", result)
			w.WriteHeader(http.StatusInternalServerError)
			return
		}

		return
	}
}

```

No código é possível ver a criação de um evento e o envio para o serviço de auditoria, que ficou da seguinte forma:

```go
package main

import (
	"context"
	"fmt"
	"log"

	cloudevents "github.com/cloudevents/sdk-go/v2"
)

func receive(event cloudevents.Event) {
	// do something with event.
	fmt.Printf("%s", event)
}

func main() {
	// The default client is HTTP.
	c, err := cloudevents.NewClientHTTP()
	if err != nil {
		log.Fatalf("failed to create client, %v", err)
	}
	if err = c.StartReceiver(context.Background(), receive); err != nil {
		log.Fatalf("failed to start receiver: %v", err)
	}
}

```


Executando os dois serviços é possível ver o funcionamento, ao enviar uma request para o *user*:


```bash
curl -X "POST" "http://localhost:3000/v1/user" \
     -H 'Accept: application/json' \
     -H 'Content-Type: application/json' \
     -d $'{
  "name": "Ozzy Osbourne",
  "password": "12345"
}'

```

O output do *user* é:

```bash
{"level":"info","service":"user","httpRequest":{"proto":"HTTP/1.1","remoteIP":"[::1]:50894","requestID":"Macbook-Air-de-Elton.local/3YUAnzEbis-000001","requestMethod":"POST","requestPath":"/v1/user","requestURL":"http://localhost:3000/v1/user"},"httpRequest":{"header":{"accept":"application/json","content-length":"52","content-type":"application/json","user-agent":"curl/8.7.1"},"proto":"HTTP/1.1","remoteIP":"[::1]:50894","requestID":"Macbook-Air-de-Elton.local/3YUAnzEbis-000001","requestMethod":"POST","requestPath":"/v1/user","requestURL":"http://localhost:3000/v1/user","scheme":"http"},"timestamp":"2024-11-28T15:52:27.947355-03:00","message":"Request: POST /v1/user"}
{"level":"warn","service":"user","httpRequest":{"proto":"HTTP/1.1","remoteIP":"[::1]:50894","requestID":"Macbook-Air-de-Elton.local/3YUAnzEbis-000001","requestMethod":"POST","requestPath":"/v1/user","requestURL":"http://localhost:3000/v1/user"},"httpResponse":{"bytes":0,"elapsed":2.33225,"status":0},"timestamp":"2024-11-28T15:52:27.949877-03:00","message":"Response: 0 Unknown"}

```

E o output do serviço de auditoria, demonstrando o recebimento do evento.

```bash
❯ go run main.go
Context Attributes,
  specversion: 1.0
  type: user.storeUser
  source: github.com/eminetto/post-cloudevents
  id: 5190bc29-a3d5-4fca-9a88-85fccffc16b6
  time: 2024-11-28T18:53:17.474154Z
  datacontenttype: application/json
Data,
  {
    "id": "8aadf8c5-9c4e-4c11-af24-beac2fb9a4b7"
  }
```

Para validar o objetivo da portabilidade, usei o SDK de Python para implementar uma versão do serviço de auditoria:

```python
from flask import Flask, request

from cloudevents.http import from_http

app = Flask(__name__)


# create an endpoint at http://localhost:/3000/
@app.route("/", methods=["POST"])
def home():
    # create a CloudEvent
    event = from_http(request.headers, request.get_data())

    # you can access cloudevent fields as seen below
    print(
        f"Found {event['id']} from {event['source']} with type "
        f"{event['type']} and specversion {event['specversion']}"
    )

    return "", 204


if __name__ == "__main__":
    app.run(port=8080)

```

E o output da aplicação mostra o recebimento do evento, sem necessidade de alteração no serviço `user`:

```bash
(.venv) eminetto@Macbook-Air-de-Elton audit-python % python3 main.py
 * Serving Flask app 'main'
 * Debug mode: off
WARNING: This is a development server. Do not use it in a production deployment. Use a production WSGI server instead.
 * Running on http://127.0.0.1:8080
Press CTRL+C to quit
Found ce1abe22-dce5-40f0-8c82-12093b707ed7 from github.com/eminetto/post-cloudevents with type user.storeUser and specversion 1.0
127.0.0.1 - - [28/Nov/2024 15:59:31] "POST / HTTP/1.1" 204 -
```


O exemplo anterior serve ao propósito de apresentar os SDKs do CloudEvents, mas ele fere um princípio das arquiteturas baseadas em eventos que é diminuir o acoplamento. A aplicação `user` conhece e está vinculada à aplicação de auditoria, o que não é uma prática recomendável. Podemos melhorar esta situação usando outros recursos do CloudEvents, como pub/sub ou adicionando algo como o Kafka. O exemplo a seguir usa o Kafka para desacoplar as duas aplicações. 

O primeiro passo foi criar um `docker-compose.yaml` para usarmos o Kafka:

```yaml
services:
  kafka:
    image: bitnami/kafka:latest
    restart: on-failure
    ports:
      - 9092:9092
    environment:
      - KAFKA_CFG_BROKER_ID=1
      - KAFKA_CFG_LISTENERS=PLAINTEXT://:9092
      - KAFKA_CFG_ADVERTISED_LISTENERS=PLAINTEXT://127.0.0.1:9092
      - KAFKA_CFG_ZOOKEEPER_CONNECT=zookeeper:2181
      - KAFKA_CFG_NUM_PARTITIONS=3
      - ALLOW_PLAINTEXT_LISTENER=yes
    depends_on:
      - zookeeper

  zookeeper:
    image: bitnami/zookeeper:latest
    ports:
      - 2181:2181
    environment:
      - ALLOW_ANONYMOUS_LOGIN=yes

```

A próxima alteração foi no serviço `user`:

```go
package main

import (
	"context"
	"encoding/json"
	"log"
	"net/http"
	"time"

	"github.com/IBM/sarama"
	"github.com/cloudevents/sdk-go/protocol/kafka_sarama/v2"
	cloudevents "github.com/cloudevents/sdk-go/v2"
	"github.com/go-chi/chi/v5"
	"github.com/go-chi/httplog"
	"github.com/google/uuid"
)

const (
	auditService = "127.0.0.1:9092"
	auditTopic   = "audit"
)

func main() {
	logger := httplog.NewLogger("user", httplog.Options{
		JSON: true,
	})
	ctx := context.Background()

	saramaConfig := sarama.NewConfig()
	saramaConfig.Version = sarama.V2_0_0_0

	sender, err := kafka_sarama.NewSender([]string{auditService}, saramaConfig, auditTopic)
	if err != nil {
		log.Fatalf("failed to create protocol: %s", err.Error())
	}

	defer sender.Close(context.Background())

	ceClient, err := cloudevents.NewClient(sender, cloudevents.WithTimeNow(), cloudevents.WithUUIDs())
	if err != nil {
		log.Fatalf("failed to create client, %v", err)
	}

	r := chi.NewRouter()
	r.Use(httplog.RequestLogger(logger))
	r.Post("/v1/user", storeUser(ctx, ceClient))

	http.Handle("/", r)
	srv := &http.Server{
		ReadTimeout:  30 * time.Second,
		WriteTimeout: 30 * time.Second,
		Addr:         ":3000",
		Handler:      http.DefaultServeMux,
	}
	err = srv.ListenAndServe()
	if err != nil {
		logger.Panic().Msg(err.Error())
	}
}

type userRequest struct {
	ID       uuid.UUID
	Name     string `json:"name"`
	Password string `json:"password"`
}

func storeUser(ctx context.Context, ceClient cloudevents.Client) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		oplog := httplog.LogEntry(r.Context())

		var ur userRequest
		err := json.NewDecoder(r.Body).Decode(&ur)
		if err != nil {
			w.WriteHeader(http.StatusBadRequest)
			oplog.Error().Msg(err.Error())
			return
		}
		ur.ID = uuid.New()
		//TODO: store user in a database

		// Create an Event.
		event := cloudevents.NewEvent()
		event.SetID(uuid.New().String())
		event.SetSource("github.com/eminetto/post-cloudevents")
		event.SetType("user.storeUser")
		event.SetData(cloudevents.ApplicationJSON, map[string]string{"id": ur.ID.String()})

		// Send that Event.
		if result := ceClient.Send(
			// Set the producer message key
			kafka_sarama.WithMessageKey(context.Background(), sarama.StringEncoder(event.ID())),
			event,
		); cloudevents.IsUndelivered(result) {
			oplog.Error().Msgf("failed to send, %v", result)
			w.WriteHeader(http.StatusInternalServerError)
			return
		}

		return
	}
}


```

Foram necessárias poucas alterações, a maioria para fazermos a conexão com o Kafka, sendo que o evento em si não mudou.

Alteração similar foi feita no serviço de auditoria:

```go
package main

import (
	"context"
	"fmt"
	"log"

	"github.com/IBM/sarama"

	"github.com/cloudevents/sdk-go/protocol/kafka_sarama/v2"
	cloudevents "github.com/cloudevents/sdk-go/v2"
)

const (
	auditService = "127.0.0.1:9092"
	auditTopic   = "audit"
	auditGroupID = "audit-group-id"
)

func receive(event cloudevents.Event) {
	// do something with event.
	fmt.Printf("%s", event)
}

func main() {
	saramaConfig := sarama.NewConfig()
	saramaConfig.Version = sarama.V2_0_0_0

	receiver, err := kafka_sarama.NewConsumer([]string{auditService}, saramaConfig, auditGroupID, auditTopic)
	if err != nil {
		log.Fatalf("failed to create protocol: %s", err.Error())
	}

	defer receiver.Close(context.Background())

	c, err := cloudevents.NewClient(receiver)
	if err != nil {
		log.Fatalf("failed to create client, %v", err)
	}

	if err = c.StartReceiver(context.Background(), receive); err != nil {
		log.Fatalf("failed to start receiver: %v", err)
	}
}

```


O output das aplicações não teve alteração.

Com a inclusão do Kafka agora as aplicações deixam de ser acopladas e não ferimos mais os princípios de uma EDA, enquanto que mantemos as vantagens providas pelos CloudEvents.

O objetivo deste post era servir como uma introdução ao padrão, bem como demonstrar a facilidade de implementação usando os SDKs. O assunto pode ser aprofundado mas espero ter atingido o objetivo e inspirado a pesquisa e uso da tecnologia.

Se você já usa/usou CloudEvents e quizer compartilhar suas experiências nos comentários vai ser de grande utilidade.

Os códigos apresentados neste post podem ser encontrados no [repositório no Github](https://github.com/eminetto/post-cloudevents).