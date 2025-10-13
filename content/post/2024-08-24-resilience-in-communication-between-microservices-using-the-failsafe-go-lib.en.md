---
title: Resilience in communication between microservices using the failsafe-go lib
date: 2024-08-24T09:00:43-03:00
draft: false
tags:
  - go
---
Let's start at the beginning. What is resilience? I like the definition in this [post](https://erikhollnagel.com/ideas/resilience-engineering.html):

> The intrinsic ability of a system to adjust its functioning prior to, during, or following changes and disturbances, so that it can sustain required operations under both expected and unexpected conditions
> 

As it is a broad term, I will focus on communication between microservices in this post. To do this, I created two services using Go: `serviceA` and `serviceB` (my creativity was not high when writing this post).
 
The initial code for both was as follows:

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

As you can see in the code, if `serviceB` has a problem, it will affect the functioning of `serviceA`, as it does not handle any communication failure. We will improve this by using lib `failsafe-go`.

According to the documentation on the [official website](https://failsafe-go.dev/):

> Failsafe-go is a library for building resilient, fault tolerant Go applications. It works by wrapping functions with one or more resilience [policies](https://failsafe-go.dev/policies), which can be combined and [composed](https://failsafe-go.dev/policies#policy-composition) as needed. 

Let's start by applying some available policies and testing their composition.


## Timeout

The first policy we will test is the simplest, including a timeout to ensure that the connection is interrupted if `serviceB` takes too long to respond and the client knows why.

The first step was to change the `serviceB` so that it includes a delay to make it easier to demonstrate the scenario:

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

After installing `failsafe-go`, using the commands:

```go
❯ cd serviceA
❯ go get github.com/failsafe-go/failsafe-go
```

The code of `serviceA/main.go` is:


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

To test how it works, I used `curl` to access the serviceA:


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

And the following output is generated by `serviceA`:


```bash
go run main.go
{"time":"2024-08-20T08:37:36.852886-03:00","level":"INFO","msg":"Connection timed out"}
{"time":"2024-08-20T08:37:36.856079-03:00","level":"ERROR","msg":"500: Internal Server Error","request":{"time":"2024-08-20T08:37:35.851262-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63409","referer":"","length":0},"response":{"time":"2024-08-20T08:37:36.856046-03:00","latency":1004819000,"status":500,"length":45},"id":""}
```

This way, it is possible to see that the client (`curl` in this case) had an effective response and that `serviceA` was no significant impact.

Let's improve the resilience of our application by investigating another beneficial policy: retry.

## Retry

Again, it was necessary to make a change to `serviceB` to add random errors:

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

To make it easier to understand, I am showing one policy at a time, which is why `serviceA` was changed to the original version and not to the version with a timeout. Later, we will examine how to compose several policies to make the application more resilient. 

The code `serviceA/main.go` looked like this:

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

This way, if `serviceB` returns the status `StatusServiceUnavailable` (code `503`), the connection will be attempted again at progressive intervals, thanks to the function configuration `WithBackoff`. The output of `serviceA`, when accessed via `curl`, should be something similar to:
 

```bash
go run main.go
{"time":"2024-08-20T08:43:38.297621-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:43:38.283715-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63542","referer":"","length":0},"response":{"time":"2024-08-20T08:43:38.297556-03:00","latency":13840708,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:43:39.946562-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:43:39.943394-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63544","referer":"","length":0},"response":{"time":"2024-08-20T08:43:39.946545-03:00","latency":3151000,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:43:40.845862-03:00","level":"INFO","msg":"Retry 1 after delay of 1000000000"}
{"time":"2024-08-20T08:43:41.85287-03:00","level":"INFO","msg":"Retry 2 after delay of 2000000000"}
{"time":"2024-08-20T08:43:43.860694-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:43:40.841468-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63545","referer":"","length":0},"response":{"time":"2024-08-20T08:43:43.860651-03:00","latency":3019287458,"status":200,"length":71},"id":""}
```

In this example, it is possible to see that errors occurred when accessing the `serviceB`, and the lib executed the connection again until it was successful. If the connection continues to give an error, the client will receive an error message `”http://localhost:3001": retries exceeded`.

Let's go deeper into resilience by adding a circuit breaker to our project.

## Circuit breaker

The circuit breaker concept is a more advanced policy that provides greater control over access to services. The pattern circuit breaker works in three states: closed (no errors), open (with errors, interrupts transmission), and semi-open (sends a limited number of requests to the service in difficulty to test its recovery).

To use this policy, I made a new version of `serviceB` so that it could generate more error scenarios and delays:

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

And the code of `serviceA`:

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

As we can see in the output of `serviceA`, the circuit breaker is working:

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

This policy allows greater control over errors, allowing `serviceB` to recover if it is experiencing a problem. 

But what do you do when `serviceB` can no longer return, for whatever reason? In these cases, we can use a fallback.

## Fallback

The idea of ​​this policy is to have an alternative if the desired service has a more severe problem and takes a long time to return. To do this, we will change the code `serviceA`:


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

In the function `newFallback`, we can see the creation of one `http.Response` that the lib will use if the user serviceB does not respond. 

This feature allows us to respond to the client while the team responsible for `serviceB` have time to get the service up and running again.

The output of `serviceA` is something similar to this:

```bash
❯ go run main.go
{"time":"2024-08-20T08:55:27.326475-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:55:27.31306-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63772","referer":"","length":0},"response":{"time":"2024-08-20T08:55:27.326402-03:00","latency":13343208,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:55:31.756765-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:55:31.754348-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63774","referer":"","length":0},"response":{"time":"2024-08-20T08:55:31.756753-03:00","latency":2404750,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:55:34.091845-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:55:33.086273-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63775","referer":"","length":0},"response":{"time":"2024-08-20T08:55:34.091812-03:00","latency":1005580625,"status":200,"length":71},"id":""}
{"time":"2024-08-20T08:55:37.386512-03:00","level":"INFO","msg":"Fallback executed result"}
{"time":"2024-08-20T08:55:37.386553-03:00","level":"INFO","msg":"200: OK","request":{"time":"2024-08-20T08:55:37.38415-03:00","method":"GET","host":"localhost:3000","path":"/","query":"","params":{},"route":"/","ip":"[::1]:63777","referer":"","length":0},"response":{"time":"2024-08-20T08:55:37.386544-03:00","latency":2393916,"status":200,"length":76},"id":""}
```

In the next step, we will combine the concepts we've seen to create a more resilient application.

## Policy composition

To do this, we need to change the code of `serviceA` so that it makes use of the policies we have seen so far:

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

In the code:

```go
roundTripper := failsafehttp.NewRoundTripper(nil, fallback, retryPolicy, circuitBreaker, timeout)
```

It is possible to view the use of all defined policies. The lib will execute it in the "rightmost" order, that is:
 

```bash
timeout -> circuitBreaker -> retryPolicy -> fallback
```

We can see the execution of the policies by observing the `serviceA` output:

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

## Conclusion

One of the advantages of microservices architecture is that we can break a complex domain into smaller, specialized services that communicate with each other to complete the necessary logic. Ensuring that this communication is resilient and will continue to work even in the face of failures and unforeseen events is fundamental. Using libraries such as `failsafe-go` makes this process easier.

You can find the codes presented in this post on my [Github](https://github.com/eminetto/post-failsafe-go/).

