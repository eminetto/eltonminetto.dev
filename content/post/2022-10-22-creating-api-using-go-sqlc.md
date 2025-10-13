---
title: "Criando uma API usando Go e sqlc"
date: 2022-10-22T13:00:19-03:00
draft: false
tags:
  - go
---

Ao escrever uma aplicação Go que trata dados em um banco de dados (neste post vou me concentrar em bancos de dados relacionais) temos algumas opções:

- escrever as consultas SQL usando alguma lib que implemente as [interfaces da stdlib](https://pkg.go.dev/database/sql)
- usar alguma lib que facilite a geração de SQL como a [Squirrel](https://github.com/Masterminds/squirrel)
- apesar de não ser tão difundido como em outras linguagens, existem alguns ORMs que podem ser usados como os listados [aqui](https://github.com/avelino/awesome-go#orm)
- usar uma ferramenta para gerar código a partir de consultas SQL

É nesta última categoria que se encaixa a ferramenta que vou apresentar neste post, o [sqlc](https://sqlc.dev). Segundo o site oficial ao usar a ferramenta vamos seguir os passos:

1. Escrever consultas SQL
2. Executar o comando `sqlc` para gerar o código que implementa interfaces `type-safe` para essas consultas
3. Escrever o código do aplicativo que chama os métodos gerados pelo `sqlc`.

Então vamos seguir estes passos para criar um exemplo, incluindo testes.

## Instalando o sqlc

Como estou usando o macOS bastou executar o comando:

```bash
brew install sqlc
```

Na [documentação oficial](https://docs.sqlc.dev/en/stable/overview/install.html) é possível ver os métodos de instalação para outros sistemas operacionais.

## Criando o projeto

Para este post eu criei um projeto com a estrutura:

```bash
├── Makefile
├── README.md
├── bin
│   └── post-sqlc
├── cmd
│   └── api
│       └── main.go
├── docker-compose.yml
├── go.mod
├── go.sum
├── internal
│   ├── api
│   │   └── api.go
│   └── http
│       └── echo
│           ├── handler.go
│           └── handler_test.go
├── person
│   ├── db
│   │   ├── db.go
│   │   ├── models.go
│   │   └── query.sql.go
│   ├── mocks
│   │   └── UseCase.go
│   ├── person.go
│   ├── query.sql
│   ├── schema.sql
│   ├── service.go
│   └── service_test.go
└── sqlc.yaml

```

Este projeto usa uma estrutura de diretórios que adotamos no [PicPay](https://picpay.com) e que vamos detalhar em um post que deve ser publicado em breve (e que eu vou divulgar aqui no meu site). Ele também usa uma forma de abstrair frameworks web que eu descrevi [neste post](https://medium.com/inside-picpay/abstraindo-bibliotecas-web-em-aplicações-go-764ebd2ba200).

Vou destacar a seguir alguns arquivos que são importantes para este post.

### docker-compose.yml

Para este exemplo eu usei `MySQL`, mas o `sqlc` também suporta `PostgreSQL` e `SQLite`. O conteúdo do `docker-compose.yml` não tem nada de especial:

```yaml
version: "3"
services:
  mysql:
    image: mariadb:latest
    command: --default-authentication-plugin=mysql_native_password
    environment:
      MYSQL_ROOT_PASSWORD: db-root-password
      MYSQL_DATABASE: post-sqlc
      MYSQL_USER: post-sqlc
      MYSQL_PASSWORD: post-sqlc
    ports:
      - "3306:3306"
    container_name: post-sqlc-mysql
    network_mode: "bridge"
```

### sqlc.yaml

Este é o arquivo de configuração que deve ser criado para que o `sqlc` saiba os detalhes da nossa aplicação:

```yaml
version: "2"
sql:
  - schema: "person/schema.sql"
    queries: "person/query.sql"
    engine: "mysql"
    gen:
      go:
        package: "db"
        out: "person/db"
```

Nele indicamos qual `engine` de banco de dados vamos usar, bem como a localização dos arquivos usados para a criação das tabelas (`schema.sql`) e onde encontram-se as `queries SQL` (`query.sql`). Outra definição importante é a definição do nome do pacote que vai ser criado (`db`) e em qual diretório os códigos serão gerados (`person/db`). Neste exemplo só vamos ter um conjunto de arquivos mas é possível termos a configuração de vários arquivos de esquema e queries, como é possível conferir na [documentação](https://docs.sqlc.dev/en/latest/reference/config.html).

### person/schema.sql

Contém a definição das tabelas do banco de dados:

```sql
create table if not exists person (id int AUTO_INCREMENT,first_name varchar(100), last_name varchar(100), created_at datetime, updated_at datetime, PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
```

### person/query.sql

Este é o arquivo mais importante para o `sqlc`. É nele que vamos descrever as `queries SQL` e o nome das funções que devem ser geradas, bem como o comportamento que elas devem ter. Para nosso exemplo o código ficou desta forma:

```sql
-- name: Get :one
select * from person where id = ?;

-- name: List :many
select id, first_name, last_name
from person
order by first_name;

-- name: Create :execresult
insert into person (
    first_name, last_name, created_at
)
values(
    ?, ?, now()
);

-- name: Delete :exec
delete from person
where id = ?;

-- name: Update :exec
update person
set first_name = ?, last_name = ?, updated_at = now()
where id = ?;

-- name: Search :many
select id, first_name, last_name from person
where first_name like ? or last_name like ?;
```

O `sqlc` usa os comentários no começo de cada `query` como insumo para a geração dos códigos Go. Por exemplo, o comentário `-- name: List :many` indica que o a função `List` vai retornar um `slice` de resultados, enquanto que o `-- name: Get :one` deve retornar apenas uma ocorrência. Na [documentação](https://docs.sqlc.dev/en/latest/reference/query-annotations.html) é possível encontrar todas as opções disponíveis.

### person/db

Ao executarmos o comando `sqlc generate` este diretório vai ser gerado com os arquivos:

- [db.go](https://github.com/eminetto/post-sqlc/blob/main/person/db/db.go) - contém as interfaces, `structs` e construtores que são usados pelo pacote
- [models.go](https://github.com/eminetto/post-sqlc/blob/main/person/db/models.go) - contém a `struct` que representa a tabela do banco de dados, que foi inferida pelo `sqlc` de acordo com a consulta `SQL`. A forma como a `struct` é gerada pode ser alterada no arquivo de configuração, conforme mostra a [documentação](https://docs.sqlc.dev/en/latest/reference/config.html#type-overriding).
- [query.sql.go](https://github.com/eminetto/post-sqlc/blob/main/person/db/query.sql.go) - contém a implementação das interfaces e é o código que vamos usar no restante da aplicação.

Lembrando que todo o conteúdo deste diretório não deve ser alterado manualmente pois será substituído a cada execução do comando `sqlc generate`.

## person/service.go

Representa a nossa camada de serviços, que vai fazer uso do código gerado pelo `sqlc` e será usado pelas outras camadas:

```go
package person

import (
	"context"
	"database/sql"
	"fmt"

	"github.com/eminetto/post-sqlc/person/db"
)

// Service define a service
type Service struct {
	r *db.Queries
}

// NewService cria um novo serviço. Lembre-se: receba interfaces, retorne structs ;)
func NewService(r *db.Queries) *Service {
	return &Service{
		r: r,
	}
}

// Get a person
func (s *Service) Get(ctx context.Context, id ID) (*Person, error) {
	p, err := s.r.Get(ctx, sql.NullInt32{Int32: int32(id), Valid: true})
	if err != nil {
		return nil, fmt.Errorf("error reading from database: %w", err)
	}
	return &Person{
		ID:       ID(p.ID.Int32),
		Name:     p.FirstName.String,
		LastName: p.LastName.String,
	}, nil
}

// Search person
func (s *Service) Search(ctx context.Context, query string) ([]*Person, error) {
	p, err := s.r.Search(ctx, db.SearchParams{
		FirstName: sql.NullString{
			String: query,
			Valid:  true,
		},
		LastName: sql.NullString{
			String: query,
			Valid:  true,
		},
	})
	if err != nil {
		return nil, fmt.Errorf("error searching from database: %w", err)
	}
	var people []*Person
	for _, j := range p {
		people = append(people, &Person{
			ID:       ID(j.ID.Int32),
			Name:     j.FirstName.String,
			LastName: j.LastName.String,
		})
	}
	return people, nil
}

// List person
func (s *Service) List(ctx context.Context) ([]*Person, error) {
	p, err := s.r.List(ctx)
	if err != nil {
		return nil, fmt.Errorf("error reading from database: %w", err)
	}
	var people []*Person
	for _, j := range p {
		people = append(people, &Person{
			ID:       ID(j.ID.Int32),
			Name:     j.FirstName.String,
			LastName: j.LastName.String,
		})
	}
	return people, nil
}

// Create a person
func (s *Service) Create(ctx context.Context, firstName, lastName string) (ID, error) {
	result, err := s.r.Create(ctx, db.CreateParams{
		FirstName: sql.NullString{
			String: firstName,
			Valid:  true,
		},
		LastName: sql.NullString{
			String: lastName,
			Valid:  true,
		},
	})
	if err != nil {
		return 0, fmt.Errorf("error creating person: %w", err)
	}
	id, err := result.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("error creating person: %w", err)
	}
	return ID(id), nil
}

// Update person data
func (s *Service) Update(ctx context.Context, e *Person) error {
	err := s.r.Update(ctx, db.UpdateParams{
		FirstName: sql.NullString{
			String: e.Name,
			Valid:  true,
		},
		LastName: sql.NullString{
			String: e.LastName,
			Valid:  true,
		},
		ID: sql.NullInt32{
			Int32: int32(e.ID),
			Valid: true,
		},
	})
	if err != nil {
		return fmt.Errorf("error updating person: %w", err)
	}
	return nil
}

// Delete remove a person
func (s *Service) Delete(ctx context.Context, id ID) error {
	err := s.r.Delete(ctx, sql.NullInt32{
		Int32: int32(id),
		Valid: true,
	})
	if err != nil {
		return fmt.Errorf("error removing person: %w", err)
	}
	return nil
}
```

Perceba que no seu construtor ele recebe uma instância de `*db.Queries`, o código que foi gerado pelo `sqlc`. O serviço faz toda a lógica para traduzir os parâmetros recebidos para o formato que é necessário para o uso da camada de persistência gerada pelo `sqlc`.

## internal/http/echo/handler.go

Neste arquivo está a lógica dos `handlers http` da nossa aplicação:

```go
package echo

import (
	"fmt"
	"net/http"

	"github.com/eminetto/post-sqlc/person"
	"github.com/labstack/echo/v4"
)

func Handlers(pService person.UseCase) *echo.Echo {
	e := echo.New()
	e.GET("/hello", Hello)
	e.GET("/hello/:lastname", GetUser(pService))
	return e
}

func Hello(c echo.Context) error {
	return c.String(http.StatusOK, "Hello, World!")
}

func GetUser(s person.UseCase) echo.HandlerFunc {
	return func(c echo.Context) error {
		lastname := c.Param("lastname")
		people, err := s.Search(c.Request().Context(), lastname)
		if err != nil {
			return c.String(http.StatusInternalServerError, err.Error())
		}
		if len(people) == 0 {
			return c.String(http.StatusNotFound, "not found")
		}
		return c.String(http.StatusOK, fmt.Sprintf("Hello %s %s", people[0].Name, people[0].LastName))
	}
}

```

Ela faz uso da camada de serviço, que por sua vez faz o acesso ao banco de dados.

### cmd/api/main.go

No arquivo `main.go` fazemos a inicialização dos recursos necessários para a execução da aplicação:

```go
package main

import (
	"database/sql"
	"fmt"
	"log"

	"github.com/eminetto/post-sqlc/internal/api"
	"github.com/eminetto/post-sqlc/internal/http/echo"
	"github.com/eminetto/post-sqlc/person"
	"github.com/eminetto/post-sqlc/person/db"
	_ "github.com/go-sql-driver/mysql"
)

//TODO replace by env vars
const (
	dbUser         = "post-sqlc"
	dbPassword     = "post-sqlc"
	database       = "post-sqlc"
	dbRootPassword = "db-root-password"
)

func main() {
	dbUri := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true", dbUser, dbPassword, "localhost", "3306", database)
	d, err := sql.Open("mysql", dbUri)
	if err != nil {
		log.Fatal(err)
	}

	queries := db.New(d)
	pService := person.NewService(queries)
	h := echo.Handlers(pService)
	err = api.Start("8000", h)
	if err != nil {
		log.Fatal("error running api", err)
	}
}

```

## Testando a camada de acesso ao banco de dados

Para implementar os testes desta camada podemos usar algumas abordagens diferentes. Para este post eu implementei duas possíveis:

- fazer `mock` do banco de dados
- usar `containers` para rodar os testes com um banco "real"

### Mock do banco de dados

Para isso eu usei a lib [go-sqlmock](https://github.com/DATA-DOG/go-sqlmock). O trecho a seguir faz parte do arquivo `person/service_test.go`:

```go
package person_test

import (
	"context"
	"errors"
	"fmt"
	"testing"
	"time"

	"github.com/DATA-DOG/go-sqlmock"
	"github.com/eminetto/post-sqlc/person"
	"github.com/eminetto/post-sqlc/person/db"
	_ "github.com/go-sql-driver/mysql"
	"github.com/stretchr/testify/assert"
)

func TestService_Get(t *testing.T) {
	d, mock, err := sqlmock.New()
	if err != nil {
		t.Fatalf("an error '%s' was not expected when opening a stub database connection", err)
	}
	defer d.Close()
	queries := db.New(d)
	t.Run("usuário encontrado", func(t *testing.T) {
		// fase: Arrange
		rows := sqlmock.NewRows([]string{"id", "name", "lastname", "created_at", "updated_at"}).
			AddRow(1, "Ozzy", "Osbourne", time.Now(), time.Now())
		mock.ExpectQuery("[A-Za-z]?select id, first_name, last_name, created_at, updated_at from person where id").
			WillReturnRows(rows)

		service := person.NewService(queries)
		// fase: Act
		found, err := service.Get(context.TODO(), person.ID(1))

		// fase: Assert
		p := &person.Person{
			ID:       1,
			Name:     "Ozzy",
			LastName: "Osbourne",
		}
		assert.Nil(t, err)
		assert.Equal(t, p, found)
	})
	t.Run("usuário não encontrado", func(t *testing.T) {
		mock.ExpectQuery("[A-Za-z]?select id, first_name, last_name, created_at, updated_at from person where id").WillReturnError(errors.New(""))
		service := person.NewService(queries)
		found, err := service.Get(context.TODO(), person.ID(1))
		assert.Nil(t, found)
		assert.Errorf(t, err, "erro lendo person do repositório: %w")
	})
}

func TestCreateWithSQLMock(t *testing.T) {
	d, mock, err := sqlmock.New()
	if err != nil {
		t.Fatalf("an error '%s' was not expected when opening a stub database connection", err)
	}
	defer d.Close()
	firstName := "Ozzy"
	lastName := "Osbourne"
	mock.ExpectExec("[A-Za-z]?insert into person").
		WithArgs(firstName, lastName).
		WillReturnResult(sqlmock.NewResult(1, 1))
	queries := db.New(d)
	service := person.NewService(queries)
	id, err := service.Create(context.TODO(), firstName, lastName)
	assert.Nil(t, err)
	assert.Equal(t, person.ID(1), id)
}
```

Neste trecho é possível ver a criação do `mock`:

```go
d, mock, err := sqlmock.New()
```

E a configuração para que ele responda o que é esperado em determinado cenário:

```go
rows := sqlmock.NewRows([]string{"id", "name", "lastname", "created_at", "updated_at"}).
			AddRow(1, "Ozzy", "Osbourne", time.Now(), time.Now())
mock.ExpectQuery("[A-Za-z]?select id, first_name, last_name, created_at, updated_at from person where id").
			WillReturnRows(rows)
```

Desta forma, ao executar o teste estamos simulando o comportamento esperado sem a necessidade de existência de um banco de dados real.

**Vantagem**

- execução rápida

**Desvantagem**

- inclusão de uma nova dependência no projeto, a `go-sqlmock`
- a definição do comportamento esperado no `mock` não é tão simples, exigindo conhecimentos em expressões regulares
- o código fica mais verboso
- como estamos simulando o comportamento de um banco de dados, possíveis erros na escrita das consultas SQL podem passar desapercebidos.

### Usando containers

A segunda forma que quero demonstrar aqui é usando `containers docker`. A ideia é que o teste faça a configuração de um `container` no começo da execução e remova o ambiente ao final. Para isso adicionei o trecho a seguir no arquivo `person/service_test.go`:

```go
func TestGetWithContainer(t *testing.T) {
	ctx := context.Background()

	container, err := person.SetupMysql(ctx)
	if err != nil {
		t.Fatal(err)
	}
	defer container.Terminate(ctx)
	d, err := sql.Open("mysql", container.URI)
	if err != nil {
		t.Error(err)
	}
	defer d.Close()
	err = person.InitMySQL(ctx, d)
	if err != nil {
		t.Fatal(err)
	}
	defer person.TruncateMySQL(ctx, d)

	firstName := "Ozzy"
	lastName := "Osbourne"

	q := db.New(d)
	s := person.NewService(q)

	id, err := s.Create(context.TODO(), firstName, lastName)
	assert.Nil(t, err)

	saved, err := s.Get(context.TODO(), id)
	assert.Nil(t, err)
	assert.Equal(t, firstName, saved.Name)
	assert.Equal(t, lastName, saved.LastName)
}
```

Criei um arquivo auxiliar chamado `person/test_helper.go` com as funções necessárias para a criação e gerenciamento do ambiente:

```go
package person

import (
	"bufio"
	"context"
	"database/sql"
	"fmt"
	"os"

	"github.com/testcontainers/testcontainers-go"
	"github.com/testcontainers/testcontainers-go/wait"
)

type MysqlDBContainer struct {
	testcontainers.Container
	URI string
}

//TODO replace by env vars
const (
	dbUser         = "post-sqlc"
	dbPassword     = "post-sqlc"
	database       = "post-sqlc"
	dbRootPassword = "db-root-password"
)

func SetupMysql(ctx context.Context) (*MysqlDBContainer, error) {
	req := testcontainers.ContainerRequest{
		Image:        "mariadb:10.9.3-jammy",
		ExposedPorts: []string{"3306/tcp"},
		WaitingFor:   wait.ForLog("Version: '10.9.3-MariaDB-1:10.9.3+maria~ubu2204'  socket: '/run/mysqld/mysqld.sock'  port: 3306  mariadb.org binary distribution"),
		Env: map[string]string{
			"MARIADB_USER":          dbUser,
			"MARIADB_PASSWORD":      dbPassword,
			"MARIADB_ROOT_PASSWORD": dbRootPassword,
			"MARIADB_DATABASE":      database,
		},
	}
	container, err := testcontainers.GenericContainer(ctx, testcontainers.GenericContainerRequest{
		ContainerRequest: req,
		Started:          true,
	})
	if err != nil {
		return nil, err
	}
	mappedPort, err := container.MappedPort(ctx, "3306")
	if err != nil {
		return nil, err
	}

	hostIP, err := container.Host(ctx)
	if err != nil {
		return nil, err
	}
	uri := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true", "root", dbRootPassword, hostIP, mappedPort.Port(), database)

	return &MysqlDBContainer{Container: container, URI: uri}, nil
}

func InitMySQL(ctx context.Context, db *sql.DB) error {
	file, err := os.Open("schema.sql")
	if err != nil {
		return err
	}
	defer file.Close()

	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		_, err := db.ExecContext(ctx, scanner.Text())
		if err != nil {
			return err
		}
	}

	if err := scanner.Err(); err != nil {
		return err
	}

	return nil
}

func TruncateMySQL(ctx context.Context, db *sql.DB) error {
	query := []string{
		fmt.Sprintf("use %s;", database),
		"truncate table person",
	}
	for _, q := range query {
		_, err := db.ExecContext(ctx, q)
		if err != nil {
			return err
		}
	}
	return nil
}
```

Ao executar o teste é possível visualizar o `container` sendo iniciado e as consultas sendo executadas.

**Vantagens**

- como estamos testando em um banco de dados real possíveis erros nas consultas SQL são percebidas mais facilmente
- o código do teste fica mais limpo

**Desvantagens**

- a velocidade da execução dos testes diminui consideravelmente, pois estamos inicializando um banco de dados a cada execução. OBS: é possível melhorarmos a velocidade reaproveitando o ambiente e apenas limpando a base de dados a cada cenário testado. Fiz um exemplo similar a esse [neste arquivo](https://github.com/eminetto/post-testes-go/pull/1/files), de outro [post](https://medium.com/inside-picpay/testes-automatizados-em-go-aa5cf9ed672e), usando a feature `Suite` da lib [testify](https://github.com/stretchr/testify)
- é possível que a criação de ambientes conflite com o serviço de CI/CD que seu projeto esteja usando, então é importante conferir isso antes de adotar essa solução

## Conclusões

Gostei bastante do uso do `sqlc` para a geração de código baseado nas consultas SQL do projeto. Acho interessante a ideia de focar nas consultas SQL e não em funções "mágicas" de um ORM, o que pode causar alguns problemas de performance.

A documentação do `sqlc` é bem completa e fácil de se usar. Outro ponto que achei legal é que ele é bem focado e não tenta abraçar todas as features de um ORM. Por exemplo, a parte de migrações não é abordada por ele e a documentação cita [outras ferramentas](https://docs.sqlc.dev/en/latest/howto/ddl.html#handling-sql-migrations) que podem ser usadas para cumprir esse importante fim.

Um ponto a ser considerado é que, ao escolher o `sqlc` como parte da arquitetura de um projeto estamos nos comprometendo com a decisão de usarmos um banco de dados relacional. Isso pode ser um problema caso exista a necessidade de alteração desta decisão no futuro. Para estes casos eu acredito que usar uma abordagem mais próxima de uma `clean architecture` possa ser mais interessante:

- criar uma interface `Repository`
- usar essa interface como dependência para o `Service`, ao invés da struct `Query` como fiz neste exemplo
- implementar a interface `Repository` escrevendo as consultas no código, talvez usando alguma solução como o `Squirrel` ou o [sqlx](https://jmoiron.github.io/sqlx/)
- caso seja necessário alterar para um banco `NoSQL` bastaria criar uma nova implementação da interface `Repository`

Resumindo, o `sqlc` me parece ser uma boa opção para ser avaliada em um projeto que tenha a necessidade de manipulação de dados em bancos relacionais.

O código deste post encontra-se neste [repositório](https://github.com/eminetto/post-sqlc).
