---
title: "Microsserviços em Go usando Go kit"
subtitle: ""
date: "2021-02-06T08:33:24+02:00"
bigimg: ""
tags:
  - go
---

Em um dos capítulos do livro [Microservice Patterns: With examples in Java](https://www.amazon.com.br/Microservice-Patterns-examples-Chris-Richardson/dp/1617294543/ref=sr_1_1?__mk_pt_BR=ÅMÅŽÕÑ&crid=5S2QOI44DDW4&dchild=1&keywords=microservices+patterns&qid=1612616717&sprefix=microservice+pa%2Caps%2C300&sr=8-1) o autor cita o padrão ["Microservice chassis"](https://microservices.io/patterns/microservice-chassis.html):

> Crie serviços em um framework ou coleção de frameworks que tratem de questões transversais como exception tracking, logging, health checks, configuração externalizada e rastreamento distribuído.

Ele vai além e cita alguns exemplos de frameworks que implementam estes conceitos em Java e em Go:

- [Gizmo](http://open.blogs.nytimes.com/2015/12/17/introducing-gizmo/?_r=2)
- [Micro](https://github.com/micro)
- [Go kit](https://github.com/go-kit/kit)

Depois de uma pesquisa escolhi o Go kit pois é um dos mais populares, está sendo atualizado em uma velocidade constante e gostei bastante da arquitetura que ele propôe.

## Arquitetura

### Service

![service](/images/posts/gokit_service.png)

Os serviços são onde toda a lógica de negócios é implementada.
No Go kit, os serviços são normalmente modelados como interfaces e as implementações dessas interfaces contêm a lógica de negócios.
A lógica de negócios não deve ter conhecimento dos conceitos das outras camadas. Por exemplo, seu serviço não deve saber nada sobre cabeçalhos HTTP ou códigos de erro gRPC.

### Endpoint

![endpoint](/images/posts/gokit_endpoint.png)

Um endpoint é como uma action/handler em um controller. Um endpoint expõe um método de serviço para o mundo externo usando a camada de transporte. Um único endpoint pode ser exposto usando vários transportes.

### Transport

![transport](/images/posts/gokit_transport.png)

O domínio de transporte está vinculado a transportes concretos como HTTP ou gRPC. Em um mundo onde os microsserviços podem oferecer suporte a um ou mais transportes, isso é muito poderoso; você pode oferecer suporte a uma API HTTP e um serviço gRPC mais recente, tudo em um único microsserviço.

## Exemplo

Vamos criar um exemplo de microsserviço usando esta arquitetura. A estrutura de diretórios ficou desta forma:

![example](/images/posts/gokit_example.png)

### Service

O código da camada de serviço neste nosso exemplo é bem simples:

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

Como a documentação do Go kit recomenda, o primeiro passo é criarmos uma `interface` para o nosso serviço, que será implementada com a nossa regra de negócio. Logo essa decisão, de criarmos uma interface, vai se mostrar útil quando formos incluir logging e monitoramento de métricas na aplicação.

Por possuir apenas regra de negócio, o teste da camada de serviço também é bem simples:

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

Vamos agora expor nossas funções para o mundo externo. Neste exemplo as duas funções vão poder ser acessadas externamente, por isso vamos criar dois endpoints. Mas nem sempre isso é verdade, dependendo do cenário você pode expor apenas algumas funções e manter as demais acessíveis apenas dentro da camada de serviço.

```go
package user

import (
	"context"

	"github.com/go-kit/kit/endpoint"
)

//definição das estruturas de entrada e saída do endpoint
type validateUserRequest struct {
	Email    string `json:"email"`
	Password string `json:"password"`
}

type validateUserResponse struct {
	Token string `json:"token,omitempty"`
	Err   string `json:"err,omitempty"` // errors don't JSON-marshal, so we use a string
}

//o endpoint vai receber uma request, converter para o formato
//desejado, invocar o serviço e retornar a struct de response
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

//definição das estruturas de entrada e saída do endpoint
type validateTokenRequest struct {
	Token string `json:"token"`
}

type validateTokenResponse struct {
	Email string `json:"email,omitempty"`
	Err   string `json:"err,omitempty"`
}

//o endpoint vai receber uma request, converter para o formato
//desejado, invocar o serviço e retornar a struct de response
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

O papel do endpoint é receber uma requisição, convertê-la para a struct esperada, invocar a camada de serviço e retornar outra struct. O detalhe é que a camada de endpoint não sabe nada sobre a camada superior, pois não faz diferença se o endpoint está sendo invocado via HTTP, gRPC ou outra forma de transporte. Ela apenas entende structs nativas da linguagem Go.

Pela sua simplicidade, o teste desta camada é igualmente fácil de se implementar:

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

Este teste poderia ser facilmente melhorado substituindo o uso do serviço por um mock que implemente a mesma interface `Service`, tornando os testes mais eficientes.

## Transport

Nesta camada podemos ter várias implementações como HTTP, gRPC, AMPQ, NATS, etc. Neste exemplo vamos expor nossos endpoins na forma de uma API HTTP. Para isso vamos criar o arquivo `transpor_http.go`:

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
	//opções fornecidas pelo Go kit para facilitar o controle de erros
	options := []httptransport.ServerOption{
		httptransport.ServerErrorLogger(logger),
		httptransport.ServerErrorEncoder(encodeErrorResponse),
	}
	//definição de um handler
	validateUserHandler := httptransport.NewServer(
		makeValidateUserEndpoint(svc), //usa o endpoint
		decodeValidateUserRequest, //converte os parâmetros recebidos via body da request na struct esperada pelo endpoint
		encodeResponse, //converte a struct retornada pelo endpoint em uma resposta json
		options...,
	)

	validateTokenHandler := httptransport.NewServer(
		makeValidateTokenEndpoint(svc),
		decodeValidateTokenRequest,
		encodeResponse,
		options...,
	)
	r := mux.NewRouter() //estou usando o Gorilla Mux, mas poderia ser qualquer outra biblioteca, ou mesmo a stdlib
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

//converte os parâmetros recebidos via body da request na struct esperada pelo endpoint
func decodeValidateUserRequest(ctx context.Context, r *http.Request) (interface{}, error) {
	var request validateUserRequest
	if err := json.NewDecoder(r.Body).Decode(&request); err != nil {
		return nil, err
	}
	return request, nil
}

//converte os parâmetros recebidos via body da request na struct esperada pelo endpoint
func decodeValidateTokenRequest(ctx context.Context, r *http.Request) (interface{}, error) {
	var request validateTokenRequest
	if err := json.NewDecoder(r.Body).Decode(&request); err != nil {
		return nil, err
	}
	return request, nil
}

//converte a struct retornada pelo endpoint em uma resposta json
func encodeResponse(ctx context.Context, w http.ResponseWriter, response interface{}) error {
	return json.NewEncoder(w).Encode(response)
}

```

O código se parece basicamente com uma série de configurações, indicando qual endpoint vai ser usado em cada endereço da API. Tentei descrever o comportamento nos comentários do código. E o teste desta camada ficou desta forma:

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

Assim como o teste da camada de endpoint, poderíamos melhorar este teste usando um mock do serviço.

## Main

No arquivo `main.go` vamos fazer a junção das camadas:

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

Aqui podemos ver outra vantagem em termos criado uma interface para nosso serviço. A função `user.NewHttpServer` espera como primeiro parâmetro algo que implemente a interface `Service`. A função `user.NewLoggingMiddleware` cria uma struct que implementa esta interface e tem dentro dela o nosso serviço original. O código do arquivo `logging.go` ficou desta forma:

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

Ele implementa todas as funções da interface, incrementando com a funcionalidade de gerar log de cada chamada de função, antes de invocar o código do serviço real. O mesmo pode ser usado para implementarmos métricas, limite de acesso a API, etc. No tutorial oficial temos [alguns exemplos](https://gokit.io/examples/stringsvc.html#application-instrumentation) disso.

Caso nosso microsserviço precisar entregar a lógica em mais formatos, como gRPC ou NATS, bastaria implementarmos estes códigos na camada de transporte indicando quais endpoints serão usados. Isso dá muita flexibilidade para o crescimento das funcionalidades sem o aumento de complexidade e gerando reuso de código.

Neste post eu foquei mais na arquitetura fornecida pelo Go kit, mas na [documentação oficial](https://pkg.go.dev/github.com/go-kit/kit) é possível ver as outras funcionalidades de `chassi` que ele fornece como: autenticação, circuit breaker, log, métricas, rate limit, service discovery, tracing, etc.

Gostei muito da arquitetura e funcionalidades que ele fornece e acredito que pode ser útil para criar serviços de maneira rápida, ordenada e eficiente.

Os códigos deste exemplo estão [neste repositório](https://github.com/eminetto/talk-microservices-gokit).
