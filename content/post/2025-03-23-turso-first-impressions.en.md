---
title: First impressions with the Turso database
date: 2025-03-23T18:00:00-03:00
draft: false
tags:
  - go
---

[Turso](https://turso.tech) is one of those projects that you look at and think, "How has no one done something like this before?"  I've been following the project since its launch, but only now have I been able to dedicate some time to testing, which I describe in this post.

Simply put, it adds a data distribution and synchronization layer using one of the most underrated databases on the market, SQLite.

To start the test, I created a free account on the company's website, which gives me [access](https://turso.tech/pricing) to enough resources for testing and personal projects.

The next step was to install the CLI on my macOS using the command:

```bash
brew install tursodatabase/tap/turso
```	

The [documentation](https://docs.turso.tech/quickstart) shows how to install it on other operating systems.
	
The next step was to authenticate

```bash
turso auth signup
```

And create the first database:

```bash
turso db create demo-post
```
	
For this example, I created a single table using the commands:

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

Since I'm going to use Go for this example, I created a new project and installed the necessary dependency:


```bash
mkdir post-turso
go mod init github.com/eminetto/post-turso
go get github.com/tursodatabase/go-libsql

```

The simplest example to test is with code similar to:

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

As you can see in the code, we need to define some environment variables, especially  `TURSO_DATABASE_URL` and `TURSO_AUTH_TOKEN`. To do this, we need to generate this data using the commands:


```bash
turso db show --url demo-post
turso db tokens create demo-post
```

These two commands will display the URL and the token, respectively, in the terminal. With this, we can configure the necessary variables:


```bash
export DBNAME="local.db"
export TURSO_DATABASE_URL="DATABASE_URL"
export TURSO_AUTH_TOKEN="AUTH_TOKEN"
export PORT="8082"

```


I will now highlight some essential points from the code above.

```go
dir, err := os.MkdirTemp("", "libsql-*")
if err != nil {
	fmt.Printf("creating temporary directory: %w", err)
	return
}

dbPath := filepath.Join(dir, os.Getenv("DBNAME"))
```

In this section, we define a local database in a temporary directory. This is one of Turso's interesting details. All writes (inserts, deletes, updates) will be performed in the remote database, identified by the URL and token generated in the previous step. Meanwhile, reads are performed in this temporary database, ensuring high reading speed.
 
But how is the data synchronized? That's what we'll configure in the next section:

```go
syncInterval := time.Second * 30

connector, err := libsql.NewEmbeddedReplicaConnector(dbPath, os.Getenv("TURSO_DATABASE_URL"),
	libsql.WithAuthToken(os.Getenv("TURSO_AUTH_TOKEN")),
	libsql.WithSyncInterval(syncInterval),
)
```

This configuration tells `libsql` to synchronize every specific interval, in this case, 30 seconds. As the [documentation](https://docs.turso.tech/sdk/go/quickstart) indicates, it is also possible to perform the synchronization manually.

The rest of the code is not new; it is just data manipulation in an SQLite database.

Another interesting feature is the dashboard with helpful information such as latency, lines written/read, database size, etc.:

[![turso_1](/images/posts/turso_1.png)](/images/posts/turso_1.png)

[![turso_2](/images/posts/turso_2.png)](/images/posts/turso_2.png)

## First impressions

As the title promises, I want to share my first impressions of the product.

When I first saw the news about the startup and the product, it was an exciting idea since SQLite is almost ubiquitous among modern programming languages. "Boosting" it with features like distribution and synchronization seems like a great business opportunity to me, and it opens up a series of possibilities. On the [website](https://turso.tech/customers), you can see some testimonials and business areas that can make use of these features.

I noticed a relatively high latency when writing data, but it seems that this is because I am using the free plan, and this changes in the [paid options](https://docs.turso.tech/cloud/durability). However, the reading performance is really attractive. I also believe that it is possible to make tunings in relation to writing, especially in environments like [AWS](https://turso.tech/blog/turso-aws-out-of-beta), but I did not perform this type of test to write this text.


If your application does more reading than writing (you can think of several scenarios such as a product catalog, an application that reads user settings and saves them in a local database, use as a cache area, etc.) Turso can bring valuable features combined with simplicity to your project.

I will keep Turso on my radar, and if I manage to apply it to a "real life" project, I will write a second post improving my opinion with more information.
I also wrote a project that is a bit more structured than the example presented here, which you can access on my [Github](https://github.com/eminetto/post-turso).

What did you think of Turso? Share your opinions in the comments.