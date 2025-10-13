---
title: Introdução ao Encore.go
date: 2025-01-25T07:10:00-03:00
draft: false
tags:
  - go
---

O [Encore.go](https://encore.dev/go) já está no meu “radar” tem um bom tempo, quando seu belo site e exemplos chamaram minha atenção em uma notícia no Hacker News. Mas minha empolgação realmente aumentou após [este post](https://encore.dev/blog/open-source-decoupled) publicado em Dezembro de 2024. Nele é anunciado que o framework, inicialmente vinculado à ferramenta [Encore Cloud](https://encore.cloud), iria se tornar um projeto independente. Eu acredito que essa decisão vai tornar o framework mais atrativo para empresas e desenvolvedores que queiram usá-lo em seus ambientes já existentes. Nada contra o Encore Cloud, que parece ser uma solução muito interessante e robusta, mas esta liberdade de escolha favorece a adoção em empresas de diferentes tamanhos.

Dado este contexto inicial, eu resolvi portar um projeto que uso para escrever textos e palestras sobre microsserviços para o Encore.go e o resultado é uma série de posts, sendo este o primeiro. A ideia inicial é dividir a série da seguinte forma:

1. Criando uma API com banco de dados (<--- você está aqui)
2. Criando uma API com autenticação
3. Comunicação via Pub/Sub
4. Deploy

É possível que durante a escrita das próximas partes eu resolva criar novos posts, mas o plano inicial está definido. Então vamos para a primeira parte.

## Criando uma API com banco de dados

Para fazermos uso do Encore uma peça fundamental é sua CLI, que vamos utilizar durante todo o ciclo de desenvolvimento. Como uso macOS, eu fiz a instalação usando o comando:

	brew install encoredev/tap/encore

Na [documentação](https://encore.dev/docs/ts/install) é possível ver as outras formas possíveis de instalação.

Com a CLI instalada podemos usá-la para criar o projeto:

{{< youtube FeCORBTdn6I >}}

Como eu escolhi a opção de criar um projeto “from scratch” o diretório criado possui apenas os arquivos com as dependências (`go.mod` e `go.sum`) e o `encore.app` que será usado pela CLI para manipular o projeto. O conteúdo inicial dele é bem simples:

```json
{
	// The app is not currently linked to the encore.dev platform.
	// Use "encore app link" to link it.
	"id": "",
}
```

Por enquanto não vai ser necessário alterarmos nada no `encore.app`, então vamos criar a estrutura do primeiro microsserviço:

```bash
❯ cd post-encore/
❯ mkdir user
❯ touch user/api.go
```

Além de criar o diretório também inicializamos um arquivo chamado `api.go` onde vamos definir nossa API. O conteúdo da primeira versão ficou desta forma:

```go
package user

import (
	"context"
)

// API defines the API for the user service
// encore: service
type API struct{}

func initAPI() (*API, error) {
	return &API{}, nil
}

// AuthParams are the parameters to the Auth method
type AuthParams struct {
	Email    string `json:"email"`
	Password string `json:"password"`
}

// AuthResponse is the response to the Auth method
type AuthResponse struct {
	Token string `json:"token"`
}

// Auth authenticates a user and returns a token
//
//encore:api public method=POST path=/v1/auth
func (a *API) Auth(ctx context.Context, p *AuthParams) (*AuthResponse, error) {
	var response AuthResponse
	return &response, nil
}

// ValidateTokenParams are the parameters to the ValidateToken method
type ValidateTokenParams struct {
	Token string `json:"token"`
}

// ValidateTokenResponse is the response to the ValidateToken method
type ValidateTokenResponse struct {
	Email string `json:"email"`
}

// ValidateToken validates a token
//
//encore:api public method=POST path=/v1/validate-token
func (a *API) ValidateToken(ctx context.Context, p *ValidateTokenParams) (*ValidateTokenResponse, error) {
	response := ValidateTokenResponse{}
	return &response, nil
}

```

O Encore usa muito o conceito de `annotations` para definir o comportamento da aplicação e com isso gerar o código necessário para a execução. O primeiro exemplo disso é a criação de um `service`:

```go
// API defines the API for the user service
// encore: service
type API struct{}

```

Outra característica comum a frameworks como o Encore é a existência de convenções. A primeira que vamos encontrar aqui é a inicialização de serviços. Como definimos um serviço chamado `API` podemos criar uma função chamada `initAPI` que será invocada pelo framework. É nesta função que vamos injetar as dependências do serviço, como vamos fazer mais a frente no projeto.

A próxima `annotation` que vemos no código é a que faz a definição de uma API, como no exemplo:

```go
// Auth authenticates a user and returns a token
//
//encore:api public method=POST path=/v1/auth
func (a *API) Auth(ctx context.Context, p *AuthParams) (*AuthResponse, error) {
	var response AuthResponse
	return &response, nil
}
```

Esta declaração diz ao framework que esta é uma API pública (vamos ver mais sobre isso nas próximas partes desta série), cujo path é `/v1/auth` e que será acessada via método `POST`. Uma API é uma função que sempre recebe um `context` e uma struct de parâmetros (neste caso `AuthParams`) e retorna uma struct de resposta (neste caso `AuthResponse`).  Uma das features interessantes que o framework nos trás é a facilidade ao acessar os valores dos parâmetros: podemos simplesmente fazer uso dos valores como `p.Email`, que faz parte da struct recebida, sem a necessidade de fazermos a conversão do JSON recebido. Mais detalhes sobre o tratamento de parâmetros e exemplos podem ser encontrados [na documentação oficial](https://encore.dev/docs/go/primitives/defining-apis).

Podemos agora executar o projeto usando a CLI:

```bash
❯ encore run
  ✔ Building Encore application graph... Done!
  ✔ Analyzing service topology... Done!
  ✔ Generating boilerplate code... Done!
  ✔ Compiling application source code... Done!
  ✔ Starting Encore application... Done!

  Encore development server running!

  Your API is running at:     http://127.0.0.1:4000
  Development Dashboard URL:  http://127.0.0.1:9400/wst7a

  New Encore release available: v1.46.1 (you have v1.45.6)
  Update with: encore version update

11:07AM INF registered API endpoint endpoint=Auth path=/v1/auth service=user
11:07AM INF registered API endpoint endpoint=ValidateToken path=/v1/validate-token service=user
11:07AM INF listening for incoming HTTP requests

```

Podemos ver que a API está disponível na url `http://127.0.0.1:4000`, assim como um `Development Dashboard`, que é um dos maiores atrativos do framework. O seu funcionamento pode ser visto a seguir:

{{< youtube RbB1xfXjDfM >}}

No vídeo é possível ver as funcionalidades interessantes que estão disponíveis, como testar a API, visualizar os traces e o componentes da aplicação. O dashboard vai se tornar ainda mais útil conforme formos adicionando camadas de complexidade ao projeto.

### Configurando o banco de dados

Para evoluirmos a aplicação, o próximo passo é a definição de um banco de dados. Para isso precisamos ter o Docker instalado e em execução, pois o Encore vai fazer uso dele para criar a imagem do banco de dados. No momento da escrita deste texto, o banco de dados disponível é o PostgreSQL. No nosso `api.go` vamos fazer a seguinte alteração:

```go
package user

import (
	"context"

	"encore.dev/storage/sqldb"
)

var db = sqldb.NewDatabase("user", sqldb.DatabaseConfig{
	Migrations: "./migrations",
})
```

Como é possível visualizar no trecho, o Encore faz uso do conceito de `migrations`, o que é algo muito útil. Foi necessário criar o diretório `user/migrations` e o arquivo `user/migrations/1_create_tables.up.sql` com o conteúdo a seguir:

```sql
create table users (id varchar(50) PRIMARY KEY ,email varchar(255),password varchar(255),first_name varchar(100), last_name varchar(100), created_at date, updated_at date);
INSERT INTO users (id, email, password, first_name, last_name, created_at, updated_at) values ('8cb2237d0679ca88db6464eac60da96345513964','eminetto@email.com','8cb2237d0679ca88db6464eac60da96345513964', 'Elton', 'Minetto', now(), null);

```

No arquivo é criado o banco de dados e também inserido um registro para usarmos nos testes via Dashboard. Mais detalhes sobre `migrations` pode ser visto na [documentação](https://encore.dev/docs/go/primitives/databases#database-migrations).

Outro ponto interessante do comando `encore run` é que ele faz o auto-reload da aplicação. Sempre que algo é alterado em um dos arquivos do projeto a aplicação é recompilada e executada novamente, então seu banco de dados deve ter sido criado com sucesso.

Para finalizar esta primeira versão do projeto, criei outros arquivos para complementar a funcionalidade. Usar um framework como o Encore facilita muito as tarefas repetitivas, como configuração de rotas, conversão de parâmetros e respostas, etc. Mas não elimina a necessidade de usarmos boas práticas de desenvolvimento, como abstrações, desacoplamento, etc. Pensando nisso, criei outros arquivos que são importantes para nosso projeto:

- [user.go](https://github.com/eminetto/post-encore/blob/main/user/user.go) que define o que é um `user` na aplicação
- [service.go](https://github.com/eminetto/post-encore/blob/main/user/service.go) que contém a regra de negócio da aplicação e será usada pela API
- [security/jwt.go](https://github.com/eminetto/post-encore/blob/main/user/security/jwt.go) que contém a lógica para a geração e validação de tokens JWT

Com estes arquivos auxiliares, a versão final da nossa API ficou da seguinte forma:

```go
package user

import (
	"context"

	"encore.app/user/security"
	"encore.dev/beta/errs"
	"encore.dev/storage/sqldb"
)

var db = sqldb.NewDatabase("user", sqldb.DatabaseConfig{
	Migrations: "./migrations",
})

// API defines the API for the user service
// encore: service
type API struct {
	Service UseCase
}

func initAPI() (*API, error) {
	return &API{Service: NewService(db)}, nil
}

// AuthParams are the parameters to the Auth method
type AuthParams struct {
	Email    string `json:"email"`
	Password string `json:"password"`
}

// AuthResponse is the response to the Auth method
type AuthResponse struct {
	Token string `json:"token"`
}

// Auth authenticates a user and returns a token
//
//encore:api public method=POST path=/v1/auth
func (a *API) Auth(ctx context.Context, p *AuthParams) (*AuthResponse, error) {
	// Construct a new error builder with errs.B()
	eb := errs.B().Meta("auth", p.Email)

	err := a.Service.ValidateUser(ctx, p.Email, p.Password)
	if err != nil {
		return nil, eb.Code(errs.Unauthenticated).Msg("invalid credentials").Err()
	}
	var response AuthResponse
	response.Token, err = security.NewToken(p.Email)
	if err != nil {
		return nil, eb.Code(errs.Internal).Msg("internal error").Err()
	}
	return &response, nil
}

// ValidateTokenParams are the parameters to the ValidateToken method
type ValidateTokenParams struct {
	Token string `json:"token"`
}

// ValidateTokenResponse is the response to the ValidateToken method
type ValidateTokenResponse struct {
	Email string `json:"email"`
}

// ValidateToken validates a token
//
//encore:api public method=POST path=/v1/validate-token
func (a *API) ValidateToken(ctx context.Context, p *ValidateTokenParams) (*ValidateTokenResponse, error) {
	// Construct a new error builder with errs.B()
	eb := errs.B().Meta("validate_token", p.Token)
	t, err := security.ParseToken(p.Token)
	if err != nil {
		return nil, eb.Code(errs.Internal).Msg("internal error").Err()
	}
	tData, err := security.GetClaims(t)
	if err != nil {
		return nil, eb.Code(errs.Internal).Msg("internal error").Err()
	}
	response := ValidateTokenResponse{
		Email: tData["email"].(string),
	}
	return &response, nil
}

```

Nesta nova versão é possível ver que inicializamos o serviço (na função `initAPI`) com a injeção da regra de negócio, bem como o tratamento de erros fornecido pelo framework.

### Testes

Outra vantagem em usarmos um framework como o Encore é que ele entrega algumas funcionalidades que auxiliam na importante tarefa de escrita de testes. Nesta primeira versão temos dois importantes componentes para testar:

**service_test.go**

```go
package user_test

import (
	"context"
	"testing"

	"encore.app/user"
	"encore.dev/et"
)

func TestService(t *testing.T) {
	ctx := context.Background()
	et.EnableServiceInstanceIsolation()
	testDB, err := et.NewTestDatabase(ctx, "user")
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}

	s := user.NewService(testDB)
	t.Run("valid user", func(t *testing.T) {
		err := s.ValidateUser(ctx, "eminetto@email.com", "12345")
		if err != nil {
			t.Fatalf("unexpected error: %v", err)
		}
	})
	t.Run("invalid user", func(t *testing.T) {
		err := s.ValidateUser(ctx, "e@email.com", "12345")
		if err == nil {
			t.Fatalf("unexpected error: %v", err)
		}
	})
	t.Run("invalid password", func(t *testing.T) {
		err := s.ValidateUser(ctx, "eminetto@email.com", "111")
		if err == nil {
			t.Fatalf("unexpected error: %v", err)
		}
	})
}

```

O destaque é o uso do pacote `"encore.dev/et"` que fornece uma forma de garantirmos que os testes podem ser executados em paralelo (`et.EnableServiceInstanceIsolation()`) e a facilidade de uso de um banco de dados exclusivo para os testes (`testDB, err := et.NewTestDatabase(ctx, "user")`). O interessante é que as migrations são usadas automaticamente, então o teste fica muito mais simples de escrever e executar.

**api_test.go**

```go
package user_test

import (
	"context"
	"testing"

	"encore.app/user"
)

type ServiceMock struct{}

func (s *ServiceMock) ValidateUser(ctx context.Context, email string, password string) error {
	return nil
}

func (s *ServiceMock) ValidatePassword(ctx context.Context, u *user.User, password string) error {
	return nil
}

func TestIntegration(t *testing.T) {
	api := &user.API{
		Service: &ServiceMock{},
	}
	email := "eminetto@email.com"
	resp, err := api.Auth(context.Background(), &user.AuthParams{
		Email:    email,
		Password: "12345",
	})
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if resp.Token == "" {
		t.Fatalf("expected token to be non-empty")
	}
	r, err := api.ValidateToken(context.Background(), &user.ValidateTokenParams{
		Token: resp.Token,
	})
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if r.Email != email {
		t.Fatalf("expected email to be %q, got %q", email, r.Email)
	}
}

```

Neste teste não foi necessário utilizar nada do framework, sendo apenas o bom e velho Go, com suas vantagens nativas.

Um detalhe importante: para executar os testes é necessário usar a CLI. Por isso, ao invés de executá-los usando o comando:

	go test ./...

É preciso usar:

	encore test ./...

Existe um plugin para o Goland que permite a execução direto pela IDE, mas ainda não existe o mesmo para o VSCode, conforme pode ser visto na [documentação oficial sobre testes](https://encore.dev/docs/go/develop/testing).

## Conclusão

Neste primeiro post o objetivo era apresentar o básico do framework e instigar sua curiosidade para os próximos capítulos desta série.

Vou deixar para tecer minhas opiniões sobre o framework na última parte da série, assim posso trazer mais argumentos para dizer se gostei ou não da experiência. Mas posso adiantar que estou me divertindo bastante com os primeiros passos. E você, nobre leitor(a)? O que está achando do Encore.go até o momento? Deixe suas impressões nos comentários.
