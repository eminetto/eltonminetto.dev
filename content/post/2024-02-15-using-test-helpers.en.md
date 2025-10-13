---
title: "Using test helpers in Go"
date: 2024-02-15T20:00:43-03:00
draft: false
tags:
  - go
---

Recently, in a code review, the great [Cassio Botaro](https://www.linkedin.com/in/cassiobotaro/) gave me a handy tip: refactor some tests to use the `test helpers` feature from the `testing` package.

The code became much more readable, so I refactored some examples I had written for a post about automated testing to demonstrate the before and after.

Let's first look at the original version of the test, in this case, an end-to-end, using [testcontainers](https://golang.testcontainers.org/).

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

	req, _ := http.NewRequest("GET", "/", nil)
	rec := httptest.NewRecorder()
	c := echo.Handlers(nil, nil, nil).NewContext(req, rec)
	c.SetPath("/hello/:lastname")
	c.SetParamNames("lastname")
	c.SetParamValues("dio")
	h := echo.GetUser(service)

	err = h(c)
	assert.Nil(t, err)
	assert.Equal(t, http.StatusOK, rec.Code)
	assert.Equal(t, "Hello Ronnie Dio", rec.Body.String())
}
```

The points we will change are:

```go
ctx := context.Background()
container, err := person.SetupMysqL(ctx)
if err != nil {
	t.Fatal(err)
}
defer container.Terminate(ctx)
```

and

```go
err = person.InitMySQL(ctx, db)
if err != nil {
	t.Fatal(err)
}
```

Let's transform the functions `person.SetupMysqL(ctx)` and `person.InitMySQL(ctx, db)` into `test helpers`.

Their original code is:

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

and

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

To transform them into `test helpers` we must pass a variable that implements the interface `testing.TB` as the first parameter:

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

As the comment at the beginning of the code points out, `testing.T` and `testing.B` both implement this interface, so we shouldn't have any problems refactoring.

The function `SetupMysqL` looked like this:

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

The main changes were:

- The function now only receives a variable that implements `testing.TB`;
- The function no longer returns an error because it now fails the test if something wrong happens;
- We added the call to `t.Helper()`, which I will explain in more detail in the following paragraphs;
- We add the call to `t.Cleanup`, which is executed at the end of the test, whether it is successful or failed. In this case, we are terminating the execution of the container.

The function `t.Helper()` affects the test results. If the test fails, let's say in this excerpt:

```go
container, err := testcontainers.GenericContainer(ctx, testcontainers.GenericContainerRequest{
		ContainerRequest: req,
		Started:          true,
})
if err != nil {
	t.Errorf("error creating container %s", err.Error())
}
```

When we include the instruction `t.Helper()`, the error result will be as follows:

```bash
mysql_test.go:17: error creating container Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?: failed to create container
```

Without the `t.Helper()`, the result is different, showing the error in the helper and not in the test:

```bash
test_helper.go:44: error creating container Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?: failed to create container
```

That way, using `t.Helper()` is more valuable to ease understanding of possible test failures.

Likewise, the function code `InitMySQL` looked like this:

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

And the test that uses the helpers has become cleaner:

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

	req, _ := http.NewRequest("GET", "/", nil)
	rec := httptest.NewRecorder()
	c := echo.Handlers(nil, nil, nil).NewContext(req, rec)
	c.SetPath("/hello/:lastname")
	c.SetParamNames("lastname")
	c.SetParamValues("dio")
	h := echo.GetUser(service)

	err = h(c)
	assert.Nil(t, err)
	assert.Equal(t, http.StatusOK, rec.Code)
	assert.Equal(t, "Hello Ronnie Dio", rec.Body.String())
}

```

This refactoring made the tests more readable and easier to maintain. Also, now it's easier to reuse the helpers in different scenarios.

What do you think? Did you already know this feature? Leave your experiences and tips in the comments.
