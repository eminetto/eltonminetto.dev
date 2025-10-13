---
title: "Tratamento de erros de aplicações CLI em Golang"
date: 2022-07-06T17:56:34-03:00
draft: false
tags:
  - go
---

Quando estou desenvolvendo alguma aplicação CLI em Go eu sempre gosto de considerar o arquivo `main.go` como

> "a porta de entrada e saída da minha aplicação”

Porque a porta de entrada? É no arquivo `main.go`, que vai ser compilado para gerar o executável da aplicação, onde é feita toda a "amarração" dos demais pacotes. É nele onde iniciamos as dependências, fazemos as configurações e a invocação dos pacotes que desempenham a lógica de negócio.

Por exemplo:

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

Nele fazemos a configuração da conexão com o banco de dados, instanciamos os serviços, passamos suas dependências, etc.

E porque ele é a porta de saída da aplicação? Considere o seguinte trecho do `main.go`:

```go
	repo := repository.NewBookMySQL(db)
	service := book.NewService(repo)
	all, err := service.SearchBooks(query)
	if err != nil {
		log.Fatal(err)
	}
```

Vamos analisar o conteúdo da função `SearchBooks`, do `Service`:

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

Perceba que ele invoca outra função, a `Search` do repositório. O código desta função é:

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

O que estas duas funções tem em comum é que ambas, ao receberem um erro, interrompem o fluxo e retornam o mais rápido possível. Elas não fazem log e nem tentam interromper a execução usando alguma função como `panic` ou `os.Exit`. Essa função é responsabilidade do main. Neste exemplo ele apenas executa o `log.Fatal(err)` mas poderíamos ter uma lógica mais avançada, como enviar o log para algum serviço externo, ou gerar algum tipo de alerta para monitoramento. Desta forma fica muito mais fácil coletar os logs, métricas, fazer tratamento avançado de erro, etc, pois o tratamento disso fica centralizado no main.

Tome cuidado em especial ao executar o `os.Exit` em uma função interna pois ao fazer isso a aplicação é interrompida imediatamente, ignorando os `defer` que você possa ter usado na main. Neste exemplo, se a função `SearchBooks` executar um `os.Exit` o `defer db.Close()` que consta no main vai ser ignorado, podendo causar problemas no banco de dados.

Não recordo de ter lido em alguma documentação sobre isso ser um padrão recomendado da comunidade, mas é uma prática que tenho usado com sucesso. Concorda com essa lógica? Outras opiniões são muito bem vindas.
