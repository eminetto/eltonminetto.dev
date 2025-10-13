---
title: Resiliência na comunicação entre microsserviços usando a lib failsafe-go
date: 2024-08-24T09:00:43-03:00
draft: false
tags:
  - go
---
Vamos começar pelo princípio… O que é resiliência? Gosto da definição deste [post](https://erikhollnagel.com/ideas/resilience-engineering.html):

> A capacidade intrínseca de um sistema de ajustar seu funcionamento antes, durante ou depois de mudanças e perturbações, de modo que ele possa sustentar as operações necessárias sob condições esperadas e inesperadas.
> 

Como é um termo bem abrangente, neste post vou dar foco na comunicação entre microsserviços. Para isso, criei dois serviços usando Go, que chamei de `serviceA` e `serviceB` (a criatividade não estava em alta no momento da escrita deste post…). 

O código inicial dos dois ficou da seguinte forma:

```go
package main

// serviceA
import (
	"encoding/json"
	"io"
	"log/slog"
	"net/http"
	"os"

	"github.com/go-chi/chi/v5"
	slogchi "github.com/samber/slog-chi"
)

func main() {
	logger := slog.New(slog.NewJSONHandler(os.Stdout, nil))
	r := chi.NewRouter()
	r.Use(slogchi.New(logger))
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		type response struct {
			Message string `json:"message"`
		}
		resp, err := http.Get("http://localhost:3001")
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		body, err := io.ReadAll(resp.Body)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		defer resp.Body.Close()
		var data response
		err = json.Unmarshal(body, &data)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"messageA": "hello from service A","messageB": "` + data.Message + `"}`))
	})
	http.ListenAndServe(":3000", r)
}

```

```go
package main

//serviceB
import (
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
)

func main() {
	r := chi.NewRouter()
	r.Use(middleware.Logger)
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"message": "hello from service B"}`))
	})
	http.ListenAndServe(":3001", r)
}

```

Como é possível visualizar no código, o caso o `serviceB` tenha algum problema ele vai afetar o funcionamento do `serviceA`, pois este não faz nenhum tratamento quanto a falha da comunicação. É este contexto que vamos melhorar com o uso da lib `failsafe-go`.

Segundo a documentação do [site oficial](https://failsafe-go.dev/):

> Failsafe-go é uma biblioteca para construir aplicativos Go resilientes e tolerantes a falhas. Ela funciona envolvendo funções com uma ou mais [políticas](https://failsafe-go.dev/policies) de resiliência, que podem ser combinadas e [compostas](https://failsafe-go.dev/policies#policy-composition) conforme necessário.
> 

Vamos começar aplicando algumas das políticas disponíveis, bem como testando a composição entre elas.

## Timeout

A primeira política que vamos testar é a mais simples, a inclusão de um `timeout` para garantir que, caso `serviceB` demore muito para responder, a conexão seja interrompida e o cliente saiba o motivo. 

O primeiro passo foi alterar o `serviceB` para que ele inclua um `delay`, para facilitar a demonstração do cenário:

```go
package main
//serviceB
import (
	"net/http"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
)

func main() {
	r := chi.NewRouter()
	r.Use(middleware.Logger)
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		time.Sleep(5 * time.Second) //add a delay to simulate a slow service
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"message": "hello from service B"}`))
	})
	http.ListenAndServe(":3001", r)
}

```

Após a instalação da failsafe-go usando os comandos:

```go
❯ cd serviceA
❯ go get github.com/failsafe-go/failsafe-go
```

O código do `serviceA/main.go` ficou da seguinte forma:

```go
package main

import (
	"encoding/json"
	"io"
	"log/slog"
	"net/http"
	"os"
	"time"

	"github.com/failsafe-go/failsafe-go"
	"github.com/failsafe-go/failsafe-go/failsafehttp"
	"github.com/failsafe-go/failsafe-go/timeout"
	"github.com/go-chi/chi/v5"
	slogchi "github.com/samber/slog-chi"
)

func main() {
	logger := slog.New(slog.NewJSONHandler(os.Stdout, nil))
	r := chi.NewRouter()
	r.Use(slogchi.New(logger))
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		type response struct {
			Message string `json:"message"`
		}
		// Create a Timeout for 1 second
		timeout := newTimeout(logger)

		// Use the Timeout with a failsafe RoundTripper
		roundTripper := failsafehttp.NewRoundTripper(nil, timeout)
		client := &http.Client{Transport: roundTripper}
		resp, err := client.Get("http://localhost:3001")
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		body, err := io.ReadAll(resp.Body)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		defer resp.Body.Close()
		var data response
		err = json.Unmarshal(body, &data)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"messageA": "hello from service A","messageB": "` + data.Message + `"}`))
	})
	http.ListenAndServe(":3000", r)
}

func newTimeout(logger *slog.Logger) timeout.Timeout[*http.Response] {
	return timeout.Builder[*http.Response](1 * time.Second).
		OnTimeoutExceeded(func(e failsafe.ExecutionDoneEvent[*http.Response]) {
			logger.Info("Connection timed out")
		}).Build()
}

```

Para testar o funcionamento usei o `curl` para acessar o `serviceA`:

```bash
❯ curl -v http://localhost:3000
* Host localhost:3000 was resolved.
* IPv6: ::1
* IPv4: 127.0.0.1
*   Trying [::1]:3000...
* Connected to localhost (::1) port 3000
> GET / HTTP/1.1
> Host: localhost:3000
> User-Agent: curl/8.7.1
> Accept: */*
>
* Request completely sent off
< HTTP/1.1 500 Internal Server Error
< Date: Fri, 23 Aug 2024 19:43:23 GMT
< Content-Length: 45
< Content-Type: text/plain; charset=utf-8
<
* Connection #0 to host localhost left intact
Get "http://localhost:3001": timeout exceeded⏎
```

E o seguinte output é gerado pelo `serviceA`:

```bash
go run main.go
{"time":"2024-08-20T08:37:36.852886-03:00","level":"INFO","msg":"Connection timed out"}
{"time":"2024-08-20T08:37:36.856079-03:00","level":"ERROR","msg":"500: Internal Server Error","request":{"time":"2024-08-20T08:37:35.851262-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63409","referer":"","length":0},"response":{"time":"2024-08-20T08:37:36.856046-03:00","latency":1004819000,"status":500,"length":45},"id":""}
```

Desta forma é possível ver que o cliente (o `curl` neste caso) teve uma resposta eficaz, e o `serviceA` não teve grandes impactos.

Vamos melhorar a resiliência da nossa aplicação investigando outra política bem útil: a `retry`.

## Retry

Novamente foi necessário fazer uma alteração no `serviceB` para adicionar erros aleatórios:

```go
package main

import (
	"math/rand"
	"net/http"
	"strconv"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
)

func main() {
	r := chi.NewRouter()
	r.Use(middleware.Logger)
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		retryAfterDelay := 1 * time.Second
		if fail() {
			w.Header().Add("Retry-After", strconv.Itoa(int(retryAfterDelay.Seconds())))
			w.WriteHeader(http.StatusServiceUnavailable)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"message": "hello from service B"}`))
	})
	http.ListenAndServe(":3001", r)
}

func fail() bool {
	if flipint := rand.Intn(2); flipint == 0 {
		return true
	}
	return false
}

```

Para facilitar o entendimento, estou mostrando uma política de cada vez, por isso o `serviceA` foi alterado em relação a versão original e não em relação a versão com o `timeout`. Mais tarde vamos ver como compor várias políticas para tornar a aplicação mais resiliente. O código do `serviceA/main.go` ficou assim:

```go
package main

import (
	"encoding/json"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"os"
	"time"

	"github.com/failsafe-go/failsafe-go"
	"github.com/failsafe-go/failsafe-go/failsafehttp"
	"github.com/failsafe-go/failsafe-go/retrypolicy"
	"github.com/go-chi/chi/v5"
	slogchi "github.com/samber/slog-chi"
)

func main() {
	logger := slog.New(slog.NewJSONHandler(os.Stdout, nil))
	r := chi.NewRouter()
	r.Use(slogchi.New(logger))
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		type response struct {
			Message string `json:"message"`
		}
		// Create a RetryPolicy that only handles 500 responses, with backoff delays between retries
		retryPolicy := newRetryPolicy(logger)

		// Use the RetryPolicy with a failsafe RoundTripper
		roundTripper := failsafehttp.NewRoundTripper(nil, retryPolicy)
		client := &http.Client{Transport: roundTripper}

		resp, err := client.Get("http://localhost:3001")
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		body, err := io.ReadAll(resp.Body)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		defer resp.Body.Close()
		var data response
		err = json.Unmarshal(body, &data)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"messageA": "hello from service A","messageB": "` + data.Message + `"}`))
	})
	http.ListenAndServe(":3000", r)
}

func newRetryPolicy(logger *slog.Logger) retrypolicy.RetryPolicy[*http.Response] {
	return retrypolicy.Builder[*http.Response]().
		HandleIf(func(response *http.Response, _ error) bool {
			return response != nil && response.StatusCode == http.StatusServiceUnavailable
		}).
		WithBackoff(time.Second, 10*time.Second).
		OnRetryScheduled(func(e failsafe.ExecutionScheduledEvent[*http.Response]) {
			logger.Info(fmt.Sprintf("Retry %d after delay of %d", e.Attempts(), e.Delay))
		}).Build()
}

```

Desta forma, caso o `serviceB` retorne o status `StatusServiceUnavailable` (status code 503), a conexão vai ser tentada novamente, em intervalos progressivos, graças a configuração da função `WithBackoff`. O output do `serviceA`, ao ser acessado via `curl` deve ser algo similar a:

 

```bash
go run main.go
{"time":"2024-08-20T08:43:38.297621-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:43:38.283715-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63542","referer":"","length":0},"response":{"time":"2024-08-20T08:43:38.297556-03:00","latency":13840708,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:43:39.946562-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:43:39.943394-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63544","referer":"","length":0},"response":{"time":"2024-08-20T08:43:39.946545-03:00","latency":3151000,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:43:40.845862-03:00","level":"INFO","msg":"Retry 1 after delay of 1000000000"}
{"time":"2024-08-20T08:43:41.85287-03:00","level":"INFO","msg":"Retry 2 after delay of 2000000000"}
{"time":"2024-08-20T08:43:43.860694-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:43:40.841468-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63545","referer":"","length":0},"response":{"time":"2024-08-20T08:43:43.860651-03:00","latency":3019287458,"status":200,"length":71},"id":""}
```

Neste exemplo é possível ver que aconteceram erros ao acessar o `serviceB` e a conexão foi tentada novamente até ter sucesso. Caso a conexão continue dando erro o cliente vai receber um erro com a mensagem `"http://localhost:3001": retries exceeded`.

Vamos avançar um pouco mais na complexidade, usando a política de `circuit breaker`.

## Circuit breaker

O conceito de `circuit breaker` é uma política mais avançada e que provê um controle maior quanto ao acesso a serviços. O padrão `circuit breaker` funciona em três estados: fechado (sem erros), aberto (com erros, interrompe transmissão) e semiaberto (envia número limitado de requests ao serviço em dificuldades para testar sua recuperação).

Para usarmos essa política foi necessário fazer uma nova versão do `serviceB` para que ele pudesse gerar mais cenários de erro e `delay`:

```go
package main

import (
	"math/rand"
	"net/http"
	"strconv"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
)

func main() {
	r := chi.NewRouter()
	r.Use(middleware.Logger)
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		retryAfterDelay := 1 * time.Second
		if fail() {
			w.Header().Add("Retry-After", strconv.Itoa(int(retryAfterDelay.Seconds())))
			w.WriteHeader(http.StatusServiceUnavailable)
			return
		}
		if sleep() {
			time.Sleep(1 * time.Second)
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"message": "hello from service B"}`))
	})
	http.ListenAndServe(":3001", r)
}

func fail() bool {
	if flipint := rand.Intn(2); flipint == 0 {
		return true
	}
	return false
}

func sleep() bool {
	if flipint := rand.Intn(2); flipint == 0 {
		return true
	}
	return false
}

```

E o código do `serviceA`:

```go
package main

import (
	"encoding/json"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"os"
	"time"

	"github.com/failsafe-go/failsafe-go/circuitbreaker"
	"github.com/failsafe-go/failsafe-go/failsafehttp"
	"github.com/go-chi/chi/v5"
	slogchi "github.com/samber/slog-chi"
)

func main() {
	logger := slog.New(slog.NewJSONHandler(os.Stdout, nil))
	r := chi.NewRouter()
	r.Use(slogchi.New(logger))
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		type response struct {
			Message string `json:"message"`
		}
		// Create a CircuitBreaker that handles 503 responses and uses a half-open delay based on the Retry-After header
		circuitBreaker := newCircuitBreaker(logger)

		// Use the RetryPolicy with a failsafe RoundTripper
		roundTripper := failsafehttp.NewRoundTripper(nil, circuitBreaker)
		client := &http.Client{Transport: roundTripper}

		sendGet := func() (*http.Response, error) {
			resp, err := client.Get("http://localhost:3001")
			return resp, err
		}
		maxRetries := 3
		resp, err := sendGet()
		for i := 0; i < maxRetries; i++ {
			if err == nil && resp != nil && resp.StatusCode != http.StatusServiceUnavailable && resp.StatusCode != http.StatusTooManyRequests {
				break
			}
			time.Sleep(circuitBreaker.RemainingDelay()) // Wait for circuit breaker's delay, provided by the Retry-After header
			resp, err = sendGet()
		}
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		body, err := io.ReadAll(resp.Body)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		defer resp.Body.Close()
		var data response
		err = json.Unmarshal(body, &data)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"messageA": "hello from service A","messageB": "` + data.Message + `"}`))
	})
	http.ListenAndServe(":3000", r)
}

func newCircuitBreaker(logger *slog.Logger) circuitbreaker.CircuitBreaker[*http.Response] {
	return circuitbreaker.Builder[*http.Response]().
		HandleIf(func(response *http.Response, err error) bool {
			return response != nil && response.StatusCode == http.StatusServiceUnavailable
		}).
		WithDelayFunc(failsafehttp.DelayFunc).
		OnStateChanged(func(event circuitbreaker.StateChangedEvent) {
			logger.Info(fmt.Sprintf("circuit breaker state changed from %s to %s", event.OldState.String(), event.NewState.String()))
		}).
		Build()
}

```

No output do `serviceA` é possível ver o circuit breaker funcionando:

```bash
❯ go run main.go
{"time":"2024-08-20T08:51:37.770611-03:00","level":"INFO","msg":"circuit breaker state changed from closed to open"}
{"time":"2024-08-20T08:51:38.771682-03:00","level":"INFO","msg":"circuit breaker state changed from open to half-open"}
{"time":"2024-08-20T08:51:38.776743-03:00","level":"INFO","msg":"circuit breaker state changed from half-open to open"}
{"time":"2024-08-20T08:51:39.777821-03:00","level":"INFO","msg":"circuit breaker state changed from open to half-open"}
{"time":"2024-08-20T08:51:39.784897-03:00","level":"INFO","msg":"circuit breaker state changed from half-open to open"}
{"time":"2024-08-20T08:51:40.786209-03:00","level":"INFO","msg":"circuit breaker state changed from open to half-open"}
{"time":"2024-08-20T08:51:40.792457-03:00","level":"INFO","msg":"circuit breaker state changed from half-open to closed"}
{"time":"2024-08-20T08:51:40.792733-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:51:37.756947-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63699","referer":"","length":0},"response":{"time":"2024-08-20T08:51:40.792709-03:00","latency":3036065875,"status":200,"length":71},"id":""}
```

Essa política permite um controle maior sobre os erros, permitindo que o `serviceB` se recupere caso esteja passando por algum problema. Mas o que fazer quando o `serviceB` não consegue mais retornar, por qualquer motivo? Nestes casos podemos usar uma política de `fallback`.

## Fallback

A ideia desta política é termos uma alternativa caso o serviço desejado esteja com algum problema mais sério e demore para retornar. Para isso vamos alterar o código do `serviceA`:

```go
package main

import (
	"bytes"
	"encoding/json"
	"io"
	"log/slog"
	"net/http"
	"os"

	"github.com/failsafe-go/failsafe-go"
	"github.com/failsafe-go/failsafe-go/failsafehttp"
	"github.com/failsafe-go/failsafe-go/fallback"
	"github.com/go-chi/chi/v5"
	slogchi "github.com/samber/slog-chi"
)

func main() {
	logger := slog.New(slog.NewJSONHandler(os.Stdout, nil))
	r := chi.NewRouter()
	r.Use(slogchi.New(logger))
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		fallback := newFallback(logger)

		roundTripper := failsafehttp.NewRoundTripper(nil, fallback)
		client := &http.Client{Transport: roundTripper}

		resp, err := client.Get("http://localhost:3001")
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		body, err := io.ReadAll(resp.Body)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		defer resp.Body.Close()
		type response struct {
			Message string `json:"message"`
		}
		var data response
		err = json.Unmarshal(body, &data)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"messageA": "hello from service A","messageB": "` + data.Message + `"}`))
	})
	http.ListenAndServe(":3000", r)
}

func newFallback(logger *slog.Logger) fallback.Fallback[*http.Response] {
	resp := &http.Response{
		StatusCode: http.StatusOK,
		Header:     map[string][]string{"Content-Type": {"application/json"}},
		Body:       io.NopCloser(bytes.NewBufferString(`{"message": "error accessing service B"}`)),
	}
	return fallback.BuilderWithResult[*http.Response](resp).
		HandleIf(func(response *http.Response, err error) bool {
			return response != nil && response.StatusCode == http.StatusServiceUnavailable
		}).
		OnFallbackExecuted(func(e failsafe.ExecutionDoneEvent[*http.Response]) {
			logger.Info("Fallback executed result")
		}).
		Build()
}

```

Na função `newFallback`  podemos ver a criação de uma `http.Response` que será usada caso o `serviceB` não responda. Com isso conseguimos dar uma resposta ao cliente, enquanto o time responsável pelo `serviceB` ganha tempo para colocar o serviço em operação novamente.

O output do `serviceA` é algo similar a isso:

```bash
❯ go run main.go
{"time":"2024-08-20T08:55:27.326475-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:55:27.31306-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63772","referer":"","length":0},"response":{"time":"2024-08-20T08:55:27.326402-03:00","latency":13343208,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:55:31.756765-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:55:31.754348-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63774","referer":"","length":0},"response":{"time":"2024-08-20T08:55:31.756753-03:00","latency":2404750,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:55:34.091845-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:55:33.086273-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63775","referer":"","length":0},"response":{"time":"2024-08-20T08:55:34.091812-03:00","latency":1005580625,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:55:37.386512-03:00","level":"INFO","msg":"Fallback executed result"}
{"time":"2024-08-20T08:55:37.386553-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:55:37.38415-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63777","referer":"","length":0},"response":{"time":"2024-08-20T08:55:37.386544-03:00","latency":2393916,"status":200,"length":76},"id":""}
```

Vamos agora juntar os conceitos que vimos até o momento, para criarmos uma aplicação mais resiliente.

# Composição de políticas

Para isso, basta alterarmos o código do `serviceA` para que ele faça uso das políticas que vimos até agora:

```go
package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"os"
	"time"

	"github.com/failsafe-go/failsafe-go"
	"github.com/failsafe-go/failsafe-go/circuitbreaker"
	"github.com/failsafe-go/failsafe-go/failsafehttp"
	"github.com/failsafe-go/failsafe-go/fallback"
	"github.com/failsafe-go/failsafe-go/retrypolicy"
	"github.com/failsafe-go/failsafe-go/timeout"
	"github.com/go-chi/chi/v5"
	slogchi "github.com/samber/slog-chi"
)

func main() {
	logger := slog.New(slog.NewJSONHandler(os.Stdout, nil))
	r := chi.NewRouter()
	r.Use(slogchi.New(logger))
	r.Get("/", func(w http.ResponseWriter, r *http.Request) {
		type response struct {
			Message string `json:"message"`
		}
		retryPolicy := newRetryPolicy(logger)
		fallback := newFallback(logger)
		circuitBreaker := newCircuitBreaker(logger)
		timeout := newTimeout(logger)

		roundTripper := failsafehttp.NewRoundTripper(nil, fallback, retryPolicy, circuitBreaker, timeout)
		client := &http.Client{Transport: roundTripper}

		sendGet := func() (*http.Response, error) {
			resp, err := client.Get("http://localhost:3001")
			return resp, err
		}
		maxRetries := 3
		resp, err := sendGet()
		for i := 0; i < maxRetries; i++ {
			if err == nil && resp != nil && resp.StatusCode != http.StatusServiceUnavailable && resp.StatusCode != http.StatusTooManyRequests {
				break
			}
			time.Sleep(circuitBreaker.RemainingDelay()) // Wait for circuit breaker's delay, provided by the Retry-After header
			resp, err = sendGet()
		}
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		body, err := io.ReadAll(resp.Body)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		defer resp.Body.Close()
		var data response
		err = json.Unmarshal(body, &data)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			w.Write([]byte(err.Error()))
			return
		}
		w.Header().Set("Content-Type", "application/json")
		w.Write([]byte(`{"messageA": "hello from service A","messageB": "` + data.Message + `"}`))
	})
	http.ListenAndServe(":3000", r)
}

func newTimeout(logger *slog.Logger) timeout.Timeout[*http.Response] {
	return timeout.Builder[*http.Response](10 * time.Second).
		OnTimeoutExceeded(func(e failsafe.ExecutionDoneEvent[*http.Response]) {
			logger.Info("Connection timed out")
		}).Build()
}

func newFallback(logger *slog.Logger) fallback.Fallback[*http.Response] {
	resp := &http.Response{
		StatusCode: http.StatusOK,
		Header:     map[string][]string{"Content-Type": {"application/json"}},
		Body:       io.NopCloser(bytes.NewBufferString(`{"message": "error accessing service B"}`)),
	}
	return fallback.BuilderWithResult[*http.Response](resp).
		HandleIf(func(response *http.Response, err error) bool {
			return response != nil && response.StatusCode == http.StatusServiceUnavailable
		}).
		OnFallbackExecuted(func(e failsafe.ExecutionDoneEvent[*http.Response]) {
			logger.Info("Fallback executed result")
		}).
		Build()
}

func newRetryPolicy(logger *slog.Logger) retrypolicy.RetryPolicy[*http.Response] {
	return retrypolicy.Builder[*http.Response]().
		HandleIf(func(response *http.Response, _ error) bool {
			return response != nil && response.StatusCode == http.StatusServiceUnavailable
		}).
		WithBackoff(time.Second, 10*time.Second).
		OnRetryScheduled(func(e failsafe.ExecutionScheduledEvent[*http.Response]) {
			logger.Info(fmt.Sprintf("Retry %d after delay of %d", e.Attempts(), e.Delay))
		}).Build()
}

func newCircuitBreaker(logger *slog.Logger) circuitbreaker.CircuitBreaker[*http.Response] {
	return circuitbreaker.Builder[*http.Response]().
		HandleIf(func(response *http.Response, err error) bool {
			return response != nil && response.StatusCode == http.StatusServiceUnavailable
		}).
		WithDelayFunc(failsafehttp.DelayFunc).
		OnStateChanged(func(event circuitbreaker.StateChangedEvent) {
			logger.Info(fmt.Sprintf("circuit breaker state changed from %s to %s", event.OldState.String(), event.NewState.String()))
		}).
		Build()
}


```

No trecho

```go
roundTripper := failsafehttp.NewRoundTripper(nil, fallback, retryPolicy, circuitBreaker, timeout)
```

É possível visualizarmos o uso de todas as políticas definidas. Elas vão ser executadas na ordem “mais a direita”, ou seja: 

```bash
timeout -> circuitBreaker -> retryPolicy -> fallback
```

Podemos ver a execução das políticas ao observarmos o output do serverA:

```bash
go run main.go
{"time":"2024-08-19T10:15:29.226553-03:00","level":"INFO","msg":"circuit breaker state changed from closed to open"}
{"time":"2024-08-19T10:15:29.226841-03:00","level":"INFO","msg":"Retry 1 after delay of 1000000000"}
{"time":"2024-08-19T10:15:30.227941-03:00","level":"INFO","msg":"circuit breaker state changed from open to half-open"}
{"time":"2024-08-19T10:15:30.234182-03:00","level":"INFO","msg":"circuit breaker state changed from half-open to open"}
{"time":"2024-08-19T10:15:30.234258-03:00","level":"INFO","msg":"Retry 2 after delay of 2000000000"}
{"time":"2024-08-19T10:15:32.235282-03:00","level":"INFO","msg":"circuit breaker state changed from open to half-open"}
{"time":"2024-08-19T10:15:42.23622-03:00","level":"INFO","msg":"Connection timed out"}
{"time":"2024-08-19T10:15:42.237942-03:00","level":"INFO","msg":"circuit breaker state changed from half-open to closed"}
{"time":"2024-08-19T10:15:42.238043-03:00","level":"ERROR","msg":"500: Internal Server Error","request":{"time":"2024-08-19T10:15:29.215709-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:52527","referer":"","length":0},"response":{"time":"2024-08-19T10:15:42.238008-03:00","latency":13022704750,"status":500,"length":45},"id":""}
{"time":"2024-08-19T10:15:56.53476-03:00","level":"INFO","msg":"circuit breaker state changed from closed to open"}
{"time":"2024-08-19T10:15:56.534803-03:00","level":"INFO","msg":"Retry 1 after delay of 1000000000"}
{"time":"2024-08-19T10:15:57.535108-03:00","level":"INFO","msg":"circuit breaker state changed from open to half-open"}
{"time":"2024-08-19T10:15:57.53889-03:00","level":"INFO","msg":"circuit breaker state changed from half-open to open"}
{"time":"2024-08-19T10:15:57.538911-03:00","level":"INFO","msg":"Retry 2 after delay of 2000000000"}
{"time":"2024-08-19T10:15:59.539948-03:00","level":"INFO","msg":"circuit breaker state changed from open to half-open"}
{"time":"2024-08-19T10:15:59.544425-03:00","level":"INFO","msg":"circuit breaker state changed from half-open to open"}
{"time":"2024-08-19T10:15:59.544575-03:00","level":"ERROR","msg":"500: Internal Server Error","request":{"time":"2024-08-19T10:15:56.5263-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:52542","referer":"","length":0},"response":{"time":"2024-08-19T10:15:59.544557-03:00","latency":3018352000,"status":500,"length":245},"id":""}
{"time":"2024-08-19T10:16:11.044207-03:00","level":"INFO","msg":"Connection timed out"}
{"time":"2024-08-19T10:16:11.046026-03:00","level":"ERROR","msg":"500: Internal Server Error","request":{"time":"2024-08-19T10:16:01.043317-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:52544","referer":"","length":0},"response":{"time":"2024-08-19T10:16:11.045601-03:00","latency":10002596334,"status":500,"length":45},"id":""}
```

# Conclusão

Uma das vantagens da arquitetura de microsserviços é podermos quebrar um domínio complexo em serviços menores e especializados, que se comunicam para completar a lógica necessária. Garantirmos que esta comunicação é resiliente e vai continuar funcionando mesmo em frente a falhas e imprevistos é algo fundamental, e o uso de bibliotecas como a failsafe-go torna esse processo mais fácil.

Os códigos apresentados neste post podem ser encontrados no meu [Github](https://github.com/eminetto/post-failsafe-go/).