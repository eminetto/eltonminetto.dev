---
title: "Error handling of CLI applications in Golang"
date: 2022-07-06T17:56:34-03:00
draft: false
tags:
  - go
---

When developing some CLI applications in Go, I always consider the `main.go` file as "the input and output port of my application."

Why the input port? It's in the `main.go` file, which we will compile to generate the application's executable, where we "bind" all the other packages. The `main.go` is where we start the dependencies, configure and invoke the packages that perform the business logic.

For instance:

```go
package main

import (
	"database/sql"
	"errors"
	"fmt"
	"log"
	"os"

	"github.com/eminetto/clean-architecture-go-v2/infrastructure/repository"
	"github.com/eminetto/clean-architecture-go-v2/usecase/book"

	"github.com/eminetto/clean-architecture-go-v2/config"
	_ "github.com/go-sql-driver/mysql"

	"github.com/eminetto/clean-architecture-go-v2/pkg/metric"
)

func handleParams() (string, error) {
	if len(os.Args) < 2 {
		return "", errors.New("Invalid query")
	}
	return os.Args[1], nil
}

func main() {
	metricService, err := metric.NewPrometheusService()
	if err != nil {
		log.Fatal(err.Error())
	}
	appMetric := metric.NewCLI("search")
	appMetric.Started()
	query, err := handleParams()
	if err != nil {
		log.Fatal(err.Error())
	}

	dataSourceName := fmt.Sprintf("%s:%s@tcp(%s:3306)/%s?parseTime=true", config.DB_USER, config.DB_PASSWORD, config.DB_HOST, config.DB_DATABASE)
	db, err := sql.Open("mysql", dataSourceName)
	if err != nil {
		log.Fatal(err.Error())
	}
	defer db.Close()
	repo := repository.NewBookMySQL(db)
	service := book.NewService(repo)
	all, err := service.SearchBooks(query)
	if err != nil {
		log.Fatal(err)
	}

	//other logic to handle the data

	appMetric.Finished()
	err = metricService.SaveCLI(appMetric)
	if err != nil {
		log.Fatal(err)
	}
}
```

In it, we configure the connection to the database, instantiate the services, pass their dependencies, etc.

And why is it the application's output port? Consider the following snippet from `main.go`:

```go
	repo := repository.NewBookMySQL(db)
	service := book.NewService(repo)
	all, err := service.SearchBooks(query)
	if err != nil {
		log.Fatal(err)
	}
```

Let's analyze the contents of the `SearchBooks` function from `Service`:

```go
func (s *Service) SearchBooks(query string) ([]*entity.Book, error) {
	books, err := s.repo.Search(strings.ToLower(query))
	if err != nil {
		return nil, fmt.Errorf("executing search: %w", err)
	}
	if len(books) == 0 {
		return nil, entity.ErrNotFound
	}
	return books, nil
}
```

Notice that it invokes another function, the `Search` of the repository. The code for this function is:

```go
func (r *BookMySQL) Search(query string) ([]*entity.Book, error) {
	stmt, err := r.db.Prepare(`select id, title, author, pages, quantity, created_at from book where title like ?`)
	if err != nil {
		return nil, err
	}
	var books []*entity.Book
	rows, err := stmt.Query("%" + query + "%")
	if err != nil {
		return nil, fmt.Errorf("parsing query: %w", err)
	}
	for rows.Next() {
		var b entity.Book
		err = rows.Scan(&b.ID, &b.Title, &b.Author, &b.Pages, &b.Quantity, &b.CreatedAt)
		if err != nil {
			return nil, fmt.Errorf("scan: %w", err)
		}
		books = append(books, &b)
	}

	return books, nil
}
```

These two functions have in common that both interrupt the flow and return as quickly as possible upon receiving an error. They do not log or attempt to stop execution using some function like `panic` or `os.Exit`. This procedure is the responsibility of `main.go`. This example just executes `log.Fatal(err),` but we could have more advanced logic, like sending the log to some external service or generating some alert for monitoring. This way, it's much easier to collect metrics, do advanced error handling, etc., because the handling of this is centralized in `main.go`.

Take special care when executing `os.Exit` in an internal function. Using `os.Exit` will immediately stop the application, ignoring any `defer` you may have used in `main.go`. In this example, if the `SearchBooks` function executes an `os.Exit`, the `defer db.Close()` in `main.go` will be ignored, which may cause problems in the database.

I don't remember reading in any documentation about this being a recommended community standard, but it's a practice I've used with success. Do you agree with this approach? Other opinions are very welcome.
