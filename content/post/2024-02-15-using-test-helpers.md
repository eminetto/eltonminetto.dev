---
title: "Usando test helpers em Go"
date: 2024-02-15T20:00:43-03:00
draft: false
tags:
  - go
---

Recentemente, em um code review, o grande [Cassio Botaro](https://www.linkedin.com/in/cassiobotaro/) me deu uma dica bem útil: refatorar alguns testes que eu estava fazendo para usar o recurso de `test helpers` do pacote `testing`.

O código ficou bem mais legível, por isso resolvi refatorar alguns exemplos que havia escrito para um [post](https://eminetto.medium.com/testes-automatizados-em-go-aa5cf9ed672e) sobre testes automatizados para demonstrar o antes e depois.

Vamos primeiro observar a versão original do teste, neste caso um `end to end`, usando [testcontainers](https://golang.testcontainers.org/).

```go
package echo_test

import (
	"context"
	"database/sql"
	"net/http"
	"net/http/httptest"
	"testing"

	"github.com/eminetto/post-tests-go/internal/http/echo"
	"github.com/eminetto/post-tests-go/person"
	"github.com/eminetto/post-tests-go/person/mysql"
	_ "github.com/go-sql-driver/mysql"
	"github.com/stretchr/testify/assert"
)

func TestGetUserE2E(t *testing.T) {
	// fase: Configure os dados de teste
	ctx := context.Background()
	container, err := person.SetupMysqL(ctx)
	if err != nil {
		t.Fatal(err)
	}
	defer container.Terminate(ctx)
	db, err := sql.Open("mysql", container.URI)
	if err != nil {
		t.Error(err)
	}
	defer db.Close()
	err = person.InitMySQL(ctx, db)
	if err != nil {
		t.Fatal(err)
	}

	repo := mysql.NewMySQL(db)
	service := person.NewService(repo)
	_, err = service.Create("Ronnie", "Dio")
	assert.Nil(t, err)

	// fase: Invoque o método sendo testado
	req, _ := http.NewRequest("GET", "/", nil)
	rec := httptest.NewRecorder()
	c := echo.Handlers(nil, nil, nil).NewContext(req, rec)
	c.SetPath("/hello/:lastname")
	c.SetParamNames("lastname")
	c.SetParamValues("dio")
	h := echo.GetUser(service)

	// fase: Confirme que os resultados esperados são retornados
	err = h(c)
	assert.Nil(t, err)
	assert.Equal(t, http.StatusOK, rec.Code)
	assert.Equal(t, "Hello Ronnie Dio", rec.Body.String())
}
```

Os pontos que vamos alterar são:

```go
ctx := context.Background()
container, err := person.SetupMysqL(ctx)
if err != nil {
	t.Fatal(err)
}
defer container.Terminate(ctx)
```

e

```go
err = person.InitMySQL(ctx, db)
if err != nil {
	t.Fatal(err)
}
```

Vamos transformar as funções `person.SetupMysqL(ctx)` e `person.InitMySQL(ctx, db)` em `test helpers`.

O código original delas é:

```go
func SetupMysqL(ctx context.Context) (*MysqlDBContainer, error) {
	req := testcontainers.ContainerRequest{
		Image:        "mariadb:11.3.1-rc-jammy",
		ExposedPorts: []string{"3306/tcp"},
		WaitingFor:   wait.ForLog("Version: '11.3.1-MariaDB-1:11.3.1+maria~ubu2204'  socket: '/run/mysqld/mysqld.sock'  port: 3306  mariadb.org binary distribution"),
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
```

e

```go
func InitMySQL(ctx context.Context, db *sql.DB) error {
	query := []string{
		fmt.Sprintf("use %s;", database),
		"create table if not exists person (id int AUTO_INCREMENT,first_name varchar(100), last_name varchar(100), created_at datetime, updated_at datetime, PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;",
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

Para transformá-las em `test helpers` precisamos passar como primeiro parâmetro uma variável que implemente a interface `testing.TB`:

```go
// TB is the interface common to T, B, and F.
type TB interface {
	Cleanup(func())
	Error(args ...any)
	Errorf(format string, args ...any)
	Fail()
	FailNow()
	Failed() bool
	Fatal(args ...any)
	Fatalf(format string, args ...any)
	Helper()
	Log(args ...any)
	Logf(format string, args ...any)
	Name() string
	Setenv(key, value string)
	Skip(args ...any)
	SkipNow()
	Skipf(format string, args ...any)
	Skipped() bool
	TempDir() string

	// A private method to prevent users implementing the
	// interface and so future additions to it will not
	// violate Go 1 compatibility.
	private()
}
```

Como o comentário no começo do código aponta, essa interface é implementada por `testing.T` e `testing.B`, então não devemos ter nenhum problema na refatoração.

O código da função `SetupMysqL` ficou desta forma:

```go
func SetupMysqL(t testing.TB) *MysqlDBContainer {
	t.Helper()
	ctx := context.TODO()
	req := testcontainers.ContainerRequest{
		Image:        "mariadb:11.3.1-rc-jammy",
		ExposedPorts: []string{"3306/tcp"},
		WaitingFor:   wait.ForLog("Version: '11.3.1-MariaDB-1:11.3.1+maria~ubu2204'  socket: '/run/mysqld/mysqld.sock'  port: 3306  mariadb.org binary distribution"),
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
		t.Errorf("error creating container %s", err.Error())
	}
	mappedPort, err := container.MappedPort(ctx, "3306")
	if err != nil {
		t.Errorf("error getting container port %s", err.Error())
	}

	hostIP, err := container.Host(ctx)
	if err != nil {
		t.Errorf("error getting container host address %s", err.Error())
	}
	uri := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true", "root", dbRootPassword, hostIP, mappedPort.Port(), database)
	t.Cleanup(func() {
		container.Terminate(ctx)
	})

	return &MysqlDBContainer{Container: container, URI: uri}
}
```

As mudanças principais foram:

- a função agora recebe apenas uma variável que implementa `testing.TB`;
- a função deixa de retornar um `error` pois agora ela falha o teste caso algo errado aconteça;
- adicionamos a chamada a `t.Helper()`, que vou explicar com mais detalhes nos próximos parágrafos;
- adicionamos a chamada a `t.Cleanup` que é executada ao final do teste, seja ele sucesso ou falha. Neste caso estamos fazendo o término da execução do `container`.

A função `t.Helper()` tem um efeito no resultado dos testes. Caso o teste falhe, digamos neste trecho:

```go
container, err := testcontainers.GenericContainer(ctx, testcontainers.GenericContainerRequest{
		ContainerRequest: req,
		Started:          true,
})
if err != nil {
	t.Errorf("error creating container %s", err.Error())
}
```

Ao incluirmos a instrução `t.Helper()` o resultado do erro vai ser o seguinte:

```bash
mysql_test.go:17: error creating container Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?: failed to create container
```

Sem o `t.Helper()` o resultado é diferente, mostrando o erro na linha do `helper` e não do teste:

```bash
test_helper.go:44: error creating container Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?: failed to create container
```

É recomendado sempre usar o `t.Helper()` para facilitar a compreensão das possíveis falhas dos testes.

Da mesma forma, o código da função `InitMySQL` ficou assim:

```go
func InitMySQL(t testing.TB, db *sql.DB) {
	t.Helper()
	ctx := context.TODO()
	query := []string{
		fmt.Sprintf("use %s;", database),
		"create table if not exists person (id int AUTO_INCREMENT,first_name varchar(100), last_name varchar(100), created_at datetime, updated_at datetime, PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;",
	}
	for _, q := range query {
		_, err := db.ExecContext(ctx, q)
		if err != nil {
			t.Errorf("error executing create query %s", err.Error())
		}
	}
}
```

E o teste que faz uso dos helpers ficou mais limpo:

```go
package echo_test

import (
	"database/sql"
	"net/http"
	"net/http/httptest"
	"testing"

	"github.com/eminetto/post-tests-go/internal/http/echo"
	"github.com/eminetto/post-tests-go/person"
	"github.com/eminetto/post-tests-go/person/mysql"
	_ "github.com/go-sql-driver/mysql"
	"github.com/stretchr/testify/assert"
)

func TestGetUserE2E(t *testing.T) {
	// fase: Configure os dados de teste
	container := person.SetupMysqL(t)
	db, err := sql.Open("mysql", container.URI)
	if err != nil {
		t.Error(err)
	}
	defer db.Close()
	person.InitMySQL(t, db)

	repo := mysql.NewMySQL(db)
	service := person.NewService(repo)
	_, err = service.Create("Ronnie", "Dio")
	assert.Nil(t, err)

	// fase: Invoque o método sendo testado
	req, _ := http.NewRequest("GET", "/", nil)
	rec := httptest.NewRecorder()
	c := echo.Handlers(nil, nil, nil).NewContext(req, rec)
	c.SetPath("/hello/:lastname")
	c.SetParamNames("lastname")
	c.SetParamValues("dio")
	h := echo.GetUser(service)

	// fase: Confirme que os resultados esperados são retornados
	err = h(c)
	assert.Nil(t, err)
	assert.Equal(t, http.StatusOK, rec.Code)
	assert.Equal(t, "Hello Ronnie Dio", rec.Body.String())
}

```

Esta refatoração tornou os testes mais legíveis e de fácil manutenção, bem como facilita o reuso dos `helpers` em diferentes cenários.

O que achou? Já conhecia o recurso? Deixe suas experiências e dicas nos comentários.
