---
title: Criando uma API com autenticação usando Encore.go
date: 2025-02-22T07:10:00-03:00
draft: false
tags:
  - go
---

Esta é a segunda parte de uma série de posts sobre o framework [Encore.go](https://encore.dev/docs/go): 

1. [Criando uma API com banco de dados](https://eltonminetto.dev/post/2025-01-25-intro-encore-part-1/) 
2. Criando uma API com autenticação (<--- você está aqui)
3. Comunicação via Pub/Sub
4. Deploy

Na primeira parte criamos uma API simples, que faz a validação de um usuário dados os parâmetros corretos. Vamos agora usar esta funcionalidade para aumentar a complexidade do projeto: adicionar uma nova API, que precisa de autenticação para ser acessada. 

O primeiro passo é criarmos um novo diretório para organizarmos o código:

```bash
mkdir feedback
cd feedback
touch api.go
```

A primeira versão do `api.go` ficou da seguinte forma:

```go
package feedback

import (
	"context"
)

// API defines the API for the user service
// encore: service
type API struct {
}

// StoreFeedbackParams represents the response of the StoreFeedback function
type StoreFeedbackParams struct {
	Title string `json:"title"`
	Body  string `json:"body"`
}

// StoreFeedbackResponse represents the response of the StoreFeedback function
type StoreFeedbackResponse struct {
	ID string `json:"id"`
}

// StoreFeedback stores feedback
//
//encore:api auth method=POST path=/v1/feedback
func (a *API) StoreFeedback(ctx context.Context, p *StoreFeedbackParams) (*StoreFeedbackResponse, error) {
	return &StoreFeedbackResponse{ID: ""}, nil
}

```

A novidade é a mudança que fizemos na `annotation` do Encore para a definição da API:

```go
//encore:api auth method=POST path=/v1/feedback
```


De acordo com a [documentação](https://encore.dev/docs/go/develop/auth) existem três configurações possíveis para o nível de acesso de uma API:

- `//encore:api public` – define uma API pública que qualquer pessoa na internet pode acessar.
- `//encore:api private` – define uma API privada que nunca é acessível ao mundo externo. Ela só pode ser invocada de outros serviços no seu aplicativo e via cron jobs.
- `//encore:api auth` – define uma API pública que qualquer pessoa pode acessar, mas que requer autenticação válida.

Como configuramos nossa API com o nível de acesso `auth` precisamos criar a lógica que vai ser responsável por esta validação. Para isso vamos criar um novo pacote no nosso projeto:

```bash
mkdir authentication
touch authentication/handler.go
```

O código do `authentication/handler.go` é:

```go
package authentication

import (
	"context"

	"encore.app/user"
	"encore.dev/beta/auth"
	"encore.dev/beta/errs"
	"github.com/google/uuid"
)

// Data is the auth data
type Data struct {
	Email string
}

// AuthHandler handle auth information
//
//encore:authhandler
func AuthHandler(ctx context.Context, token string) (auth.UID, *Data, error) {
	if token == "" {
		return "", nil, &errs.Error{
			Code:    errs.Unauthenticated,
			Message: "invalid token",
		}
	}
	resp, err := user.ValidateToken(ctx, &user.ValidateTokenParams{Token: token})
	if err != nil {
		return "", nil, &errs.Error{
			Code:    errs.Unauthenticated,
			Message: "invalid token",
		}
	}
	return auth.UID(uuid.New().String()), &Data{Email: resp.Email}, nil
}
```

A `annotation` `//encore:authhandler` indica ao framework que este código deve ser executado sempre que uma API requer autenticação para ser acessada. O framework vai automaticamente tentar acessar um token que deve ser enviado na requisição usando-se o *header* *Authorization*. Esta informação vai ser passada como parâmetro (*token*) para a função *AuthHandler* (pode ser outro nome, pois o que importa é a *annotation*). Configurações de autenticação mais avançadas, como outras variáveis e *cookies* podem ser configurados, como cita [a documentação](https://encore.dev/docs/go/develop/auth#accepting-structured-auth-information).

É obrigatório que a função retorne um valor para `auth.UID` e, opcionalmente, pode retornar mais dados, como o que eu fiz neste exemplo. 

Agora podemos alterar a nossa API para que ela faça uso dos dados da autenticação:

```go
// StoreFeedback stores feedback
//
//encore:api auth method=POST path=/v1/feedback
func (a *API) StoreFeedback(ctx context.Context, p *StoreFeedbackParams) (*StoreFeedbackResponse, error) {
	eb := errs.B().Meta("store_feedback", p.Title)
	var email string
	data := auth.Data()
	if data != nil {
		email = data.(*authentication.Data).Email
	}
	if email == "" {
		return nil, eb.Code(errs.Unauthenticated).Msg("unauthenticated").Err()
	}
	f := &Feedback{
		Email: email,
		Title: p.Title,
		Body:  p.Body,
	}
	id, err := a.Service.Store(ctx, f)
	if err != nil {
		return nil, eb.Code(errs.Internal).Msg("internal error").Err()
	}
	return &StoreFeedbackResponse{ID: id}, nil
}
```

Exemplo da API sendo invocada com um token gerado pela API que desenvolvi no post passado:

```bash
curl '127.0.0.1:4000/v1/feedback' \
-H 'Authorization:Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJlbWFpbCI6ImVtaW5ldHRvQGVtYWlsLmNvbSIsImV4cCI6MTc0MDIzNTcyMiwiaWF0IjoxNzQwMjMyMDkyLCJuYmYiOjE3NDAyMzIwOTJ9._7BZwT3rveDV8gN9f2pBCy1D6_ZA17uRKIOAd7GVKLU' \
-d '{"title":"title","body":"body of feedback"}'
```



Podemos também escrever um teste para cobrir esta funcionalidade:

```go
package feedback_test

import (
	"context"
	"encore.app/authentication"
	"encore.app/feedback"
	"encore.dev/et"
	"github.com/google/uuid"
	"testing"
)

type ServiceMock struct{}

func (s *ServiceMock) Store(ctx context.Context, f *feedback.Feedback) (string, error) {
	return uuid.New().String(), nil
}

func TestStoreFeedback(t *testing.T) {
	api := feedback.API{
		Service: &ServiceMock{},
	}
	et.OverrideAuthInfo("uuid", &authentication.Data{Email: "eminetto@email.com"})
	p := feedback.StoreFeedbackParams{
		Title: "title",
		Body:  "body",
	}

	resp, err := api.StoreFeedback(context.Background(), &p)
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if resp.ID == "" {
		t.Fatalf("expected ID to be non-empty")
	}
}

```

O ponto importante é o uso da função `et.OverrideAuthInfo("uuid", &authentication.Data{Email: "eminetto@email.com"})` que é uma facilidade que o framework fornece para a escrita de testes.

O código completo desta funcionalidade pode ser visto neste [repositório](https://github.com/eminetto/post-encore).

# Conclusão

Gostei bastante de mais esta funcionalidade pois é algo comum e potencialmente repetitivo, então é importante que o framework auxilie neste processo. Na documentação é possível ver exemplos mais complexos, com integração com serviços de autenticação como o *Auth0*. 

Outro ponto que eu achei bem interessante vai além do código. Quando estava escrevendo o código do teste que apresentei aqui eu fiquei com dúvidas sobre como passar os dados de autenticação. Depois de ler a documentação e não encontrar a solução eu entrei no [Discord](https://encore.dev/discord) do projeto e fiz uma pergunta sobre o assunto. Em pleno sábado de manhã, em menos de 30 minutos um membro do time do framework me ajudou a resolver o problema. Pontos extras pela responsividade e gentileza, mas fica aqui a sugestão de uma revisão na documentação para incluir um exemplo de como implementar este teste.

Continuo empolgado com o framework e já pensando no próximo texto da série. E você, nobre leitor(a)? O que está achando do Encore? 