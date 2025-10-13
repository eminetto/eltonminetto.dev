---
title: "Microservices in Go using the Go kit "
subtitle: ""
date: "2021-02-16T08:33:24+02:00"
bigimg: ""
tags:
  - go
---

In one of the chapters of the book [Microservice Patterns: With examples in Java](https://www.amazon.com.br/Microservice-Patterns-examples-Chris-Richardson/dp/1617294543/ref=sr_1_1?__mk_pt_BR=ÅMÅŽÕÑ&crid=5S2QOI44DDW4&dchild=1&keywords=microservices+patterns&qid=1612616717&sprefix=microservice+pa%2Caps%2C300&sr=8-1) the author mentions the ["Microservice chassis"](https://microservices.io/patterns/microservice-chassis.html) pattern:

> Build your microservices using a microservice chassis framework, which handles cross-cutting concerns, such as exception tracking, logging, health checks, externalized configuration, and distributed tracing.

He goes further and gives some examples of frameworks that implement these concepts in Java and Go:

- [Gizmo](http://open.blogs.nytimes.com/2015/12/17/introducing-gizmo/?_r=2)
- [Micro](https://github.com/micro)
- [Go kit](https://github.com/go-kit/kit)

After some research I chose the Go kit as it is one of the most popular, it is being updated at a constant speed and I liked the architecture that it proposes.

## Architecture

### Service

![service](/images/posts/gokit_service.png)

Services are where all of the business logic is implemented. In Go kit, services are typically modeled as interfaces, and implementations of those interfaces contain the business logic. Go kit services should strive to abide the Clean Architecture or the Hexagonal Architecture. That is, the business logic should not know of transport-domain concepts: your service shouldn’t know anything about HTTP headers, or gRPC error codes.

### Endpoint

![endpoint](/images/posts/gokit_endpoint.png)

An endpoint is like an action/handler on a controller; it’s where safety and antifragile logic lives. If you implement two transports (HTTP and gRPC), you might have two methods of sending requests to the same endpoint.

### Transport

![transport](/images/posts/gokit_transport.png)

The transport domain is bound to concrete transports like HTTP or gRPC. In a world where microservices may support one or more transports, this is very powerful; you can support a legacy HTTP API and a newer RPC service, all in a single microservice.

## Example

Let's create an example of a microservice using this architecture. The directory structure looks like this:

![example](/images/posts/gokit_example.png)

### Service

The service layer code in this example is very simple:

```go
package user

import (
	"auth/security"
	"context"
	"errors"
)

type Service interface {
	ValidateUser(ctx context.Context, mail, password string) (string, error)
	ValidateToken(ctx context.Context, token string) (string, error)
}

var (
	ErrInvalidUser  = errors.New("Invalid user")
	ErrInvalidToken = errors.New("Invalid token")
)

type service struct{}

func NewService() *service {
	return &service{}
}

func (s *service) ValidateUser(ctx context.Context, email, password string) (string, error) {
	//@TODO create validation rules, using databases or something else
	if email == "eminetto@gmail.com" && password != "1234567" {
		return "nil", ErrInvalidUser
	}
	token, err := security.NewToken(email)
	if err != nil {
		return "", err
	}
	return token, nil
}

func (s *service) ValidateToken(ctx context.Context, token string) (string, error) {
	t, err := security.ParseToken(token)
	if err != nil {
		return "", ErrInvalidToken
	}
	tData, err := security.GetClaims(t)
	if err != nil {
		return "", ErrInvalidToken
	}
	return tData["email"].(string), nil
}
```

As the Go kit documentation recommends, the first step is to create an `interface` for our service, which will be implemented with our business logic. Soon, this decision to create an interface will prove useful when we include logging and monitoring metrics in the application.

Because it only has business rules, the service layer test is also very simple:

```go
package user_test

import (
	"auth/user"
	"context"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestValidateUser(t *testing.T) {
	service := user.NewService()
	t.Run("invalid user", func(t *testing.T) {
		_, err := service.ValidateUser(context.Background(), "eminetto@gmail.com", "invalid")
		assert.NotNil(t, err)
		assert.Equal(t, "Invalid user", err.Error())
	})
	t.Run("valid user", func(t *testing.T) {
		token, err := service.ValidateUser(context.Background(), "eminetto@gmail.com", "1234567")
		assert.Nil(t, err)
		assert.NotEmpty(t, token)
	})
}

```

## Endpoint

We will now expose our functions to the outside world. In this example the two functions will be able to be accessed externally, so we will create two endpoints. But this is not always true. Depending on the scenario you can expose only a few functions and keep the others accessible only within the service layer.

```go
package user

import (
	"context"

	"github.com/go-kit/kit/endpoint"
)

//definition of endpoint input and output structures
type validateUserRequest struct {
	Email    string `json:"email"`
	Password string `json:"password"`
}

type validateUserResponse struct {
	Token string `json:"token,omitempty"`
	Err   string `json:"err,omitempty"` // errors don't JSON-marshal, so we use a string
}

//the endpoint will receive a request, convert to the desired
//format, invoke the service and return the response structure
func makeValidateUserEndpoint(svc Service) endpoint.Endpoint {
	return func(ctx context.Context, request interface{}) (interface{}, error) {
		req := request.(validateUserRequest)
		token, err := svc.ValidateUser(ctx, req.Email, req.Password)
		if err != nil {
			return validateUserResponse{"", err.Error()}, err
		}
		return validateUserResponse{token, ""}, err
	}
}

//definition of endpoint input and output structures
type validateTokenRequest struct {
	Token string `json:"token"`
}

type validateTokenResponse struct {
	Email string `json:"email,omitempty"`
	Err   string `json:"err,omitempty"`
}

//the endpoint will receive a request, convert to the desired
//format, invoke the service and return the response structure
func makeValidateTokenEndpoint(svc Service) endpoint.Endpoint {
	return func(ctx context.Context, request interface{}) (interface{}, error) {
		req := request.(validateTokenRequest)
		email, err := svc.ValidateToken(ctx, req.Token)
		if err != nil {
			return validateTokenResponse{"", err.Error()}, err
		}
		return validateTokenResponse{email, ""}, err
	}
}

```

The role of the endpoint is to receive a request, convert it to the expected struct, invoke the service layer, and return another struct. The endpoint layer does not know anything about the upper layer, because it makes no difference whether the endpoint is being invoked via HTTP, gRPC, or another form of transport.

Because of its simplicity, testing this layer is equally easy to implement:

```go
package user

import (
	"context"
	"testing"
)

func TestMakeValidateUserEndpoint(t *testing.T) {
	s := NewService()
	endpoint := makeValidateUserEndpoint(s)
	t.Run("valid user", func(t *testing.T) {
		req := validateUserRequest{
			Email:    "eminetto@gmail.com",
			Password: "1234567",
		}
		_, err := endpoint(context.Background(), req)
		if err != nil {
			t.Errorf("expected %v received %v", nil, err)
		}
	})
	t.Run("invalid user", func(t *testing.T) {
		req := validateUserRequest{
			Email:    "eminetto@gmail.com",
			Password: "123456",
		}
		_, err := endpoint(context.Background(), req)
		if err == nil {
			t.Errorf("expected %v received %v", ErrInvalidUser, err)
		}
	})
}

```

This test could be improved by replacing the use of the service with a mock that implements the same `Service` interface, making the tests more efficient.

## Transport

In this layer, we can have several implementations like HTTP, gRPC, AMPQ, NATS, etc. In this example, we are going to expose our endpoints in the form of an HTTP API. So, we will create the file `transpor_http.go`:

```go
package user

import (
	"context"
	"encoding/json"
	"net/http"

	"github.com/go-kit/kit/log"
	httptransport "github.com/go-kit/kit/transport/http"
	"github.com/gorilla/mux"
)

func NewHttpServer(svc Service, logger log.Logger) *mux.Router {
	//options provided by the Go kit to facilitate error control
	options := []httptransport.ServerOption{
		httptransport.ServerErrorLogger(logger),
		httptransport.ServerErrorEncoder(encodeErrorResponse),
	}
	//definition of a handler
	validateUserHandler := httptransport.NewServer(
		makeValidateUserEndpoint(svc), //use the endpoint
		decodeValidateUserRequest, //converts the parameters received via the request body into the struct expected by the endpoint
		encodeResponse, //converts the struct returned by the endpoint to a json response
		options...,
	)

	validateTokenHandler := httptransport.NewServer(
		makeValidateTokenEndpoint(svc),
		decodeValidateTokenRequest,
		encodeResponse,
		options...,
	)
	r := mux.NewRouter() //I'm using Gorilla Mux, but it could be any other library, or even the stdlib
	r.Methods("POST").Path("/v1/auth").Handler(validateUserHandler)
	r.Methods("POST").Path("/v1/validate-token").Handler(validateTokenHandler)
	return r
}

func encodeErrorResponse(_ context.Context, err error, w http.ResponseWriter) {
	if err == nil {
		panic("encodeError with nil error")
	}
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(codeFrom(err))
	json.NewEncoder(w).Encode(map[string]interface{}{
		"error": err.Error(),
	})
}

func codeFrom(err error) int {
	switch err {
	case ErrInvalidUser:
		return http.StatusNotFound
	case ErrInvalidToken:
		return http.StatusUnauthorized
	default:
		return http.StatusInternalServerError
	}
}

//converts the parameters received via the request body into the struct expected by the endpoint
func decodeValidateUserRequest(ctx context.Context, r *http.Request) (interface{}, error) {
	var request validateUserRequest
	if err := json.NewDecoder(r.Body).Decode(&request); err != nil {
		return nil, err
	}
	return request, nil
}

//converts the parameters received via the request body into the struct expected by the endpoint
func decodeValidateTokenRequest(ctx context.Context, r *http.Request) (interface{}, error) {
	var request validateTokenRequest
	if err := json.NewDecoder(r.Body).Decode(&request); err != nil {
		return nil, err
	}
	return request, nil
}

//converts the struct returned by the endpoint to a json response
func encodeResponse(ctx context.Context, w http.ResponseWriter, response interface{}) error {
	return json.NewEncoder(w).Encode(response)
}

```

The code looks like a series of settings, indicating which endpoint will be used at each API address. I tried to describe the behavior in the code comments. And the test of this layer looked like this:

```go
package user

import (
	"net/http"
	"net/http/httptest"
	"os"
	"strings"
	"testing"

	"github.com/go-kit/kit/log"
)

func TestHTTP(t *testing.T) {
	var logger log.Logger
	logger = log.NewLogfmtLogger(os.Stderr)
	logger = log.With(logger, "listen", "8081", "caller", log.DefaultCaller)
	s := NewService()
	r := NewHttpServer(s, logger)
	srv := httptest.NewServer(r)

	for _, testcase := range []struct {
		method, url, body string
		want              int
	}{
		{"POST", srv.URL + "/v1/auth", `{"email": "eminetto@gmail.com", "password":"1234567"}`, http.StatusOK},
		{"GET", srv.URL + "/v1/auth", `{"email": "eminetto@gmail.com", "password":"1234567"}`, http.StatusMethodNotAllowed},
		{"POST", srv.URL + "/v1/auth", `{"email": "eminetto@gmail.com", "password":"invalid"}`, http.StatusNotFound},
		{"POST", srv.URL + "/v1/validate-token", `{"token": "invalid"}`, http.StatusUnauthorized},
	} {
		req, _ := http.NewRequest(testcase.method, testcase.url, strings.NewReader(testcase.body))
		resp, _ := http.DefaultClient.Do(req)
		if testcase.want != resp.StatusCode {
			t.Errorf("%s %s %s: want %d have %d", testcase.method, testcase.url, testcase.body, testcase.want, resp.StatusCode)
		}

	}
}

```

Just like testing the endpoint layer, we could improve this test using a mock of the service.

## Main

In the `main.go` file we are going to use all the layers:

```go
package main

import (
	"auth/user"
	"net/http"
	"os"

	"github.com/go-kit/kit/log"
)

func main() {

	var logger log.Logger
	logger = log.NewLogfmtLogger(os.Stderr)
	logger = log.With(logger, "listen", "8081", "caller", log.DefaultCaller)

	svc := user.NewLoggingMiddleware(logger, user.NewService())
	r := user.NewHttpServer(svc, logger)
	logger.Log("msg", "HTTP", "addr", "8081")
	logger.Log("err", http.ListenAndServe(":8081", r))
}
```

Here we can see another advantage in having created an interface for our service. The `user.NewHttpServer` function expects as a first parameter something that implements the `Service` interface. The `user.NewLoggingMiddleware` function creates a struct that implements this interface and has our original service inside it. The code for the `logging.go` file looks like this:

```go
package user

import (
	"context"
	"time"

	"github.com/go-kit/kit/log"
)

func NewLoggingMiddleware(logger log.Logger, next Service) logmw {
	return logmw{logger, next}
}

type logmw struct {
	logger log.Logger
	Service
}

func (mw logmw) ValidateUser(ctx context.Context, email, password string) (token string, err error) {
	defer func(begin time.Time) {
		_ = mw.logger.Log(
			"method", "validateUser",
			"input", email,
			"err", err,
			"took", time.Since(begin),
		)
	}(time.Now())

	token, err = mw.Service.ValidateUser(ctx, email, password)
	return
}

func (mw logmw) ValidateToken(ctx context.Context, token string) (email string, err error) {
	defer func(begin time.Time) {
		_ = mw.logger.Log(
			"method", "validateToken",
			"input", token,
			"err", err,
			"took", time.Since(begin),
		)
	}(time.Now())

	email, err = mw.Service.ValidateToken(ctx, token)
	return
}

```

It implements all the functions of the interface, adding the functionality of logging each function call, before invoking the code of the real service. The same can be used to implement metrics, limit access to API, etc. In the official tutorial, we have [some examples](https://gokit.io/examples/stringsvc.html#application-instrumentation) of this.

If our microservice needs to deliver the logic in more formats, such as gRPC or NATS, we would only need to implement these codes in the transport layer indicating which endpoints will be used. This gives a lot of flexibility for the growth of functionalities without increasing complexity.

In this post, I focused more on the architecture provided by the Go kit, but in the [official documentation](https://pkg.go.dev/github.com/go-kit/kit), you can see the other `chassis` features that it provides as authentication, circuit breaker, log, metrics, rate limit, service discovery, tracing, etc.

I liked the architecture and features it provides and I believe it can be useful to create services in a fast, clean and efficient way.

The codes for this example are [in this repository](https://github.com/eminetto/talk-microservices-gokit).
