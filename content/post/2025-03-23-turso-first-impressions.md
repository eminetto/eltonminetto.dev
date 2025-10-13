---
title: Primeiras impressões com o banco de dados Turso
date: 2025-03-23T18:00:00-03:00
draft: false
tags:
  - go
---

O [Turso](https://turso.tech) é um daqueles projetos que você olha e pensa “como ninguém havia feito algo assim antes?”. Venho acompanhando o projeto desde seu lançamento mas somente agora consegui dedicar um tempo para fazer alguns testes, que descrevo neste post. 

Simplificando bastante, ele adiciona uma camada de distribuição e sincronização de dados usando um dos bancos de dados mais menosprezados do mercado, o SQLite.

Para começar o teste eu fiz uma conta free no site da empresa, que [dá direito](https://turso.tech/pricing) a uma boa quantidade de recursos para testes e projetos pessoais. 

O próximo passo foi instalar o CLI no meu macOS, usando o comando:

```bash
brew install tursodatabase/tap/turso
```	
	
Na [documentação](https://docs.turso.tech/quickstart) é possível ver como fazer a instalação em outros sistemas operacionais.

O próximo passo foi fazer a autenticação:

```bash
turso auth signup
```

E criar o primeiro banco de dados:

```bash
turso db create demo-post
```
	
Para este exemplo eu criei uma tabela bem simples, usando os comandos:

```bash
turso db shell demo-post


CREATE TABLE books (
  ID INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  author TEXT,
  category int
);
.quit
```	

Como vou usar Go para este exemplo eu criei um novo projeto e instalei a dependência necessária:

```bash
mkdir post-turso
go mod init github.com/eminetto/post-turso
go get github.com/tursodatabase/go-libsql

```

O exemplo mais simples para testar é com um código similar a:

```go 
package main

import (
	"database/sql"
	"fmt"
	"github.com/tursodatabase/go-libsql"
	"os"
	"path/filepath"
	"time"
)

func main() {
	dir, err := os.MkdirTemp("", "libsql-*")
	if err != nil {
		fmt.Printf("creating temporary directory: %w", err)
		return
	}

	dbPath := filepath.Join(dir, os.Getenv("DBNAME"))
	syncInterval := time.Second * 30

	connector, err := libsql.NewEmbeddedReplicaConnector(dbPath, os.Getenv("TURSO_DATABASE_URL"),
		libsql.WithAuthToken(os.Getenv("TURSO_AUTH_TOKEN")),
		libsql.WithSyncInterval(syncInterval),
	)

	if err != nil {
		fmt.Printf("creating connector: %w", err)
		return
	}
	db := sql.OpenDB(connector)
	defer func() {
		err := os.RemoveAll(dir)
		if err != nil {
			fmt.Printf("removing temporary directory: %w", err)
		}
		err = connector.Close()
		if err != nil {
			fmt.Printf("closing connector: %w", err)
		}
		err = db.Close()
		if err != nil {
			fmt.Printf("closing repository: %w", err)
		}
	}()
	id, err := insert(db)
	if err != nil {
		fmt.Printf("inserting: %w", err)
		return
	}
	fmt.Println("Inserted ", id)
	err = showAll(db)
	if err != nil {
		fmt.Printf("showing all: %w", err)
		return
	}
}

func insert(db *sql.DB) (int64, error) {
	stmt, err := db.Prepare(`
		insert into books (title, author, category) 
		values(?,?,?)`)
	if err != nil {
		return 0, fmt.Errorf("preparing statement: %w", err)
	}
	result, err := stmt.Exec(
		"Foundation",
		"Isaac Asimov",
		1, //read
	)
	if err != nil {
		return 0, fmt.Errorf("executing statement: %w", err)
	}
	err = stmt.Close()
	if err != nil {
		return 0, fmt.Errorf("closing statement: %w", err)
	}
	id, err := result.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("getting last insert ID: %w", err)
	}

	return id, nil
}

func showAll(db *sql.DB) error {
	rows, err := db.Query("SELECT * FROM books")
	if err != nil {
		return fmt.Errorf("selecting books: %w", err)
	}
	defer rows.Close()

	for rows.Next() {
		var id int64
		var title string
		var author string
		var category int64

		if err := rows.Scan(&id, &title, &author, &category); err != nil {
			return fmt.Errorf("scanning book: %w", err)
		}

		fmt.Println(id, title, author, category)
	}

	if err := rows.Err(); err != nil {
		return fmt.Errorf("interacting with books: %w", err)
	}
	return nil
}

```

Como é possível visualizar no código, precisamos definir algumas variáveis de ambiente, em especial a `TURSO_DATABASE_URL` e a `TURSO_AUTH_TOKEN`. Para isso precisamos gerar estes dados usando os comandos:

```bash
turso db show --url demo-post
turso db tokens create demo-post
```

Estes dois comandos vão mostrar no terminal a url e o token, respectivamente. Com isso podemos exportar as variáveis necessárias:

```bash
export DBNAME="local.db"
export TURSO_DATABASE_URL="DATABASE_URL"
export TURSO_AUTH_TOKEN="AUTH_TOKEN"
export PORT="8082"

```


Vou agora destacar alguns pontos importantes do código acima. 

```go
dir, err := os.MkdirTemp("", "libsql-*")
if err != nil {
	fmt.Printf("creating temporary directory: %w", err)
	return
}

dbPath := filepath.Join(dir, os.Getenv("DBNAME"))
```

Neste trecho definimos um banco de dados local, em um diretório temporário. Isso é um dos detalhes interessantes do Turso. Todas as escritas (inserts, deletes, updates) vão ser realizados no banco de dados remoto, identificado pela URL e token gerados no passo anterior. Enquanto isso, as leituras são realizadas neste banco de dados temporário, garantindo uma grande velocidade na leitura. 

Mas e como os dados são sincronizados? É o que vamos configurar no próximo trecho:

```go
syncInterval := time.Second * 30

connector, err := libsql.NewEmbeddedReplicaConnector(dbPath, os.Getenv("TURSO_DATABASE_URL"),
	libsql.WithAuthToken(os.Getenv("TURSO_AUTH_TOKEN")),
	libsql.WithSyncInterval(syncInterval),
)
```

Isso indica à `libsql` para que ela faça uma sincronização a cada intervalo determinado, neste caso, 30 segundos. Também é possível realizar a sincronização manualmente, conforme a [documentação](https://docs.turso.tech/sdk/go/quickstart) indica.

O restante do código não tem muita novidade, sendo a manipulação de dados em um banco de dados SQLite.

Outra feature interessante é o dashboard com informações importantes e úteis como latência, linhas escritas/lidas, tamanho do banco de dados, etc:

[![turso_1](/images/posts/turso_1.png)](/images/posts/turso_1.png)

[![turso_2](/images/posts/turso_2.png)](/images/posts/turso_2.png)

## Primeiras impressões

Como o título promete, quero aqui tecer minhas primeiras impressões quanto ao produto.

Quando vi a primeira notícia sobre a startup e o produto eu achei a ideia bem interessante, pois o SQLite é quase que onipresente entre as linguagens de programação modernas. “Turbinar” ele com features como distribuição e sincronização me parece uma ótima oportunidade de negócio e que abre uma série de possibilidades. No [site](https://turso.tech/customers) é possível vermos alguns depoimentos, bem como áreas de negócio que podem fazer uso destas features.

Eu notei uma latência relativamente alta para realizar a escrita dos dados, mas me parece que é devido ao fato de eu estar usando o plano free e que isso [muda nos planos pagos](https://docs.turso.tech/cloud/durability). Mas a performance de leitura é realmente bem atrativa. Também acredito que seja possível fazer tunnings em relação a escrita, especialmente em ambientes como a [AWS](https://turso.tech/blog/turso-aws-out-of-beta), mas não realizei este tipo de teste para escrever este texto.

Se a sua aplicação faz mais leituras do que escritas (é possível pensar em vários cenários como um catálogo de produtos, uma aplicação que lê as configurações dos usuários e guarda em um banco local, o uso como área de cache, etc) o Turso pode trazer funcionalidades importante aliadas a simplicidade ao seu projeto.

Vou manter o Turso no meu radar e caso eu consiga aplicá-lo em algum projeto “vida real” eu escrevo um segundo post aprimorando minha opinião com mais informações. 

Também escrevi um projeto um pouco mais estruturado do que o exemplo apresentado aqui, que pode ser visualizado [no meu Github](https://github.com/eminetto/post-turso).

O que achou do Turso? Compartilhe suas opiniões nos comentários.