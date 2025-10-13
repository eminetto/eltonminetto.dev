---
title: "Creating an API using Go and sqlc"
date: 2022-10-22T13:00:19-03:00
draft: false
tags:
  - go
---

When writing a Go application that handles data in a database (in this post, I will focus on relational databases), we have a few options:

- write SQL queries using some lib that implements the [stdlib interfaces](https://pkg.go.dev/database/sql)
- use some lib that facilitates SQL generation, like [Squirrel](https://github.com/Masterminds/squirrel)
- although not as widespread as in other languages, we can use some ORMs, like the ones listed [here](https://github.com/avelino/awesome-go#orm)
- use a tool to generate code from SQL queries

I will present in this post a project that fits in the last category: [sqlc](https://sqlc.dev).

According to the official website, to use the tool, we will follow the steps:

1. Write SQL queries
2. Run the `sqlc` command to generate code that implements type-safe interfaces for these queries
3. Write application code that calls the methods created by `sqlc`.

So let's follow these steps to create an example, including tests.

## Installing sqlc

As I'm using macOS, I just ran the command:

```bash
brew install sqlc
```

In the official [documentation](https://docs.sqlc.dev/en/stable/overview/install.html), you can see the installation methods for other operating systems.

## Creating the project

For this post, I created a project with the structure:

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

This project uses a directory structure we adopted at [PicPay](https://picpay.com), and I will detail it in a post that should be published soon. It also uses a technique to abstract web frameworks I described in this [post](https://medium.com/inside-picpay/abstracting-web-libraries-in-go-applications-166feeaf6aff).

I will highlight below some files that are important for this post.

### docker-compose.yml

For this example, I used `MySQL`, but `sqlc` also supports `PostgreSQL` and `SQLite`. The content of `docker-compose.yml` is nothing special:

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

This file contains the configuration that one must create so that `sqlc` knows the details of our application:

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

In it, we indicate which database engine we will use, the location of the files used to create the tables (`schema.sql`), and where the SQL queries (`query.sql`) live. Another important definition is the name of the package that will be created (`db`) and in which directory the codes will be generated (`person/db`). In this example, we will only have a set of files, but it is possible to have the configuration of several schema files and queries, as you can see in the [documentation](https://docs.sqlc.dev/en/latest/reference/config.html).

### person/schema.sql

Contains the definition of the database tables:

```sql
create table if not exists person (id int AUTO_INCREMENT,first_name varchar(100), last_name varchar(100), created_at datetime, updated_at datetime, PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
```

### person/query.sql

This file contains crucial information for `sqlc`. It is where we will describe the SQL queries, the names of the functions that `sqlc` will generate, and the behavior they must have. For our example, the code looks like this:

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

`sqlc` uses comments at the beginning of each query as input for generating Go code. For example, the statement `-- name: List :many` indicates that the `List` function will return a slice of results, whereas the ` -- name: Get :one` should return only one occurrence. In the [documentation](https://docs.sqlc.dev/en/latest/reference/query-annotations.html), we can find all the available options.

### person/db

When we run the `sqlc generate` command, it will create this directory with the files:

- [db.go](https://github.com/eminetto/post-sqlc/blob/main/person/db/db.go) - contains the interfaces, structs, and constructors
- [models.go](https://github.com/eminetto/post-sqlc/blob/main/person/db/models.go) - contains the struct representing the database table, which was inferred by `sqlc` according to the SQL query. The way `sqlc` generates the struct can be changed in the configuration file, as shown in [documentation](https://docs.sqlc.dev/en/latest/reference/config.html#type-overriding).
- [query.sql.go](https://github.com/eminetto/post-sqlc/blob/main/person/db/query.sql.go) - contains the implementation of the interfaces and is the code that we will use in the rest of the application.

Remember that we shouldn't manually change the content of this directory, or we will lose its content, as `sqlc` will replace it.

## person/service.go

Represents our services layer, which will make use of the code generated by `sqlc` and will the other layers use:

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

Notice that in its constructor, it receives an instance of `*db.Queries`, the code generated by `sqlc.` Then, the service does all the logic to translate the received parameters into the format needed to use the persistence layer created by `sqlc.`

## internal/http/echo/handler.go

In this file is the logic of the `http handlers` of our application:

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

It uses the service layer, which provides access to the database.

### cmd/api/main.go

In the `main.go` file, we initialize the resources needed to run the application:

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

## Testing the Database Access Layer

We can use a few different approaches to implement the tests of this layer. For this post, I implemented two possible ones:

- `mock` the database
- use `containers` to run tests with a "real" database

### Mocking the database

For that, I used the lib [go-sqlmock](https://github.com/DATA-DOG/go-sqlmock). So, for example, the following snippet is part of the `person/service_test.go` file:

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
	t.Run("user found", func(t *testing.T) {
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
	t.Run("user not found", func(t *testing.T) {
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

In this snippet, you can see the creation of the `mock`:

```go
d, mock, err := sqlmock.New()
```

And the configuration so that it responds to what we expect in a given scenario:

```go
rows := sqlmock.NewRows([]string{"id", "name", "lastname", "created_at", "updated_at"}).
			AddRow(1, "Ozzy", "Osbourne", time.Now(), time.Now())
mock.ExpectQuery("[A-Za-z]?select id, first_name, last_name, created_at, updated_at from person where id").
			WillReturnRows(rows)
```

This way, when executing the test, we simulate the expected behavior without needing a "real" database.

**The good stuff**

- fast execution

**Not so good**

- adding a new dependency to the project, `go-sqlmock.`
- the definition of expected behavior in `mock` is not so simple, requiring knowledge of regular expressions
- the code becomes more verbose
- As we are simulating the behavior of a database, possible errors in the SQL queries can go unnoticed.

### Using containers

The second way I want to demonstrate here is by using Docker containers. The idea is that the test configures a container at the beginning and removes the environment at the end. For that, I added the following snippet in the `person/service_test.go` file:

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

I created a file called `person/test_helper.go` with the necessary functions for creating and managing the environment:

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

When running the test, it is possible to visualize the container starting and executing the queries.

**The good stuff**

- as we are testing in a "real" database, you can quickly find errors in the SQL queries
- test code is cleaner

**Not so good**

- The test execution speed slows as we initialize a database at each execution. We can improve the speed by reusing the environment and cleaning the database for each tested scenario.
- It's possible that creating environments conflicts with your project's CI/CD service, so it's important to check this out before adopting this solution.

## Conclusions

I find the idea of focusing on SQL queries and not on the "magic" functions of an ORM interesting, especially because an ORM can sometimes cause performance problems.

The `sqlc` documentation is complete and easy to use. Another point I thought was remarkable is that it's very focused and doesn't try to embrace all the features of an ORM. For example, it does not cover the migrations feature, and the [documentation](https://docs.sqlc.dev/en/latest/howto/ddl.html#handling-sql-migrations) mentions other tools we can use to fulfill this.

One point to consider is that by choosing `sqlc` as part of a project's architecture, we are committing ourselves to the decision to use a relational database. This decision can be a problem if there is a need to change technology in the future. For these cases, I believe that using a closer approach to a `clean architecture` might be more interesting:

- Create a `Repository` interface
- Use this interface as a dependency for the `Service` instead of the struct `Query.`
- Implement the `Repository` interface by writing the queries in code, perhaps using some solution like `Squirrel` or [sqlx](https://jmoiron.github.io/sqlx/).
- If it is necessary to change to a `NoSQL` database, it would be enough to create a new implementation of the `Repository` interface.

In summary, `sqlc` seems to be an excellent option to be evaluated in a project that needs to manipulate data in relational databases.

You can find the code for this post in this [repository](https://github.com/eminetto/post-sqlc).
