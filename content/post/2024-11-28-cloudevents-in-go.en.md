---
title: Using CloudEvents in Go
date: 2024-11-28T07:00:43-03:00
draft: false
tags:
  - go
---

Adopting an event-driven architecture (EDA) to increase scalability and reduce coupling between components/services is relatively common in complex environments.

While this approach solves a number of problems, one of the challenges faced by teams is standardizing events to ensure compatibility between all components. To mitigate this challenge, we can use the [CloudEvents](https://cloudevents.io) project.

The project aims to be a [specification](https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md) for standardizing and describing events, bringing consistency, accessibility, and portability. Another advantage is that the project provides a series of SDKs to accelerate team adoption in addition to being a specification.

In this post, I want to demonstrate the use of the [Go SDK](https://github.com/cloudevents/sdk-go) (with a special appearance by the [Python SDK](https://github.com/cloudevents/sdk-python) ) in a fictitious project.


Let's consider an environment composed of two microservices: a `user`, which manages users (CRUD), and an audit service, which stores important events in the environment for future analysis.

The service code of the `user` service is as follows:


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

In the code, you can see the creation of an event and its sending to the audit service, which looks like this:


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


By running both services, you can see how they work by sending a request to the `user` :

```bash
curl -X "POST" "http://localhost:3000/v1/user" \
     -H 'Accept: application/json' \
     -H 'Content-Type: application/json' \
     -d $'{
  "name": "Ozzy Osbourne",
  "password": "12345"
}'

```

The `user` output is:

```bash
{"level":"info","service":"user","httpRequest":{"proto":"HTTP/1.1","remoteIP":"[::1]:50894","requestID":"Macbook-Air-de-Elton.local/3YUAnzEbis-000001","requestMethod":"POST","requestPath":"/v1/user","requestURL":"http://localhost:3000/v1/user"},"httpRequest":{"header":{"accept":"application/json","content-length":"52","content-type":"application/json","user-agent":"curl/8.7.1"},"proto":"HTTP/1.1","remoteIP":"[::1]:50894","requestID":"Macbook-Air-de-Elton.local/3YUAnzEbis-000001","requestMethod":"POST","requestPath":"/v1/user","requestURL":"http://localhost:3000/v1/user","scheme":"http"},"timestamp":"2024-11-28T15:52:27.947355-03:00","message":"Request: POST /v1/user"}
{"level":"warn","service":"user","httpRequest":{"proto":"HTTP/1.1","remoteIP":"[::1]:50894","requestID":"Macbook-Air-de-Elton.local/3YUAnzEbis-000001","requestMethod":"POST","requestPath":"/v1/user","requestURL":"http://localhost:3000/v1/user"},"httpResponse":{"bytes":0,"elapsed":2.33225,"status":0},"timestamp":"2024-11-28T15:52:27.949877-03:00","message":"Response: 0 Unknown"}

```

The output of the audit service demonstrates the receipt of the event.

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

To validate the portability goal, I used the Python SDK to implement a version of the audit service:


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

The application output shows the receipt of the event without the need for changes to the service `user`:

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


The previous example introduces the CloudEvents SDKs, but it violates a principle of event-based architectures: loosen coupling. The application user is aware of and tied to the auditing application, which is not a good practice. We can improve this situation by using other CloudEvents features, such as pub/sub, or by adding something like Kafka. The following example uses Kafka to decouple the two applications.

The first step was to create one `docker-compose.yaml` to use Kafka:

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

The following change was in the service `user`:

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

A few changes were needed, mainly to make a connection with Kafka, but the event itself did not change.

I made a similar change to the audit service:

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


The output of the applications stays the same.

With the inclusion of Kafka, we decoupled the applications, no longer violating the principles of EDA while maintaining the advantages provided by CloudEvents.

The goal of this post was to introduce the standard and demonstrate the ease of implementation using the SDKs. I could cover the subject in more depth, but I hope I have achieved the objective and inspired research and use of the technology. 

It would be very useful if you already use/have used CloudEvents and wanted to share your experiences in the comments.

You can find the codes I presented in this post in the repository on [GitHub](https://github.com/eminetto/post-cloudevents).
