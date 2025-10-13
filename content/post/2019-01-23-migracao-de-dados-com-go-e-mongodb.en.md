---
title: "Data Migration with Golang and MongoDB"
subtitle: ""
date: "2019-01-23T10:09:24+02:00"
bigimg: ""
tags:
  - go
---

One item addressed by the [twelve-factor app](https://12factor.net) method is the automation of administrative processes, such as script execution and data migration. This is exactly what I will talk about in this post: how we automate the migration of data using Go and MongoDB.

<!--more-->

In [Codenation](https://codenation.dev), we chose Go as the main programming language for product development. Thanks to this choice, and the adoption of [Clean Architecture](/en/post/2018-03-05-clean-architecture-using-go/), we could quickly create APIs, lambda functions , command line apps (CLI), bots , etc. And we can reuse the logic of the layers of Clean Architecture to speed up the development and evolution of the product.

But for the data migration process we had not found a simple way to implement it in Go. So, we started by using a solution in [node.js](https://www.npmjs.com/package/migrate-mongo).

The solution worked satisfactorily for several months, but we were having little productivity in creating the migration scripts. The main reason was our lack of familiarity with the nuances of node.js, especially the asynchronous behavior of queries executed in MongoDB. And as we could not reuse the logic implemented in Go made us "reinvent the wheel" in a few moments.

So we did a new research and arrived at a solution in Go. The first step came from the discovery of this project:

https://github.com/xakep666/mongo-migrate

We made some [contributions](https://github.com/xakep666/mongo-migrate/pull/1) in the project and we came up with a solution that is working well for us.

The first step was the creation of a CLI application responsible for creating and executing new migrations. The code for this app looks like this:

[![main](/images/posts/migrations_main.png)](/images/posts/migrations_main.png)

Let’s start by creating a new migration, with the command:

    go run cmd/migrations/main.go new alter-user-data

The result is something like:

    2019/01/23 10:02:36 New migration created: ./migrations/20190123100236_alter-user-data.go

What the command did was copy the file **migrations/template.go** creating a new migration. This is the content of a _template.go_:

[![main](/images/posts/migrations_template.png)](/images/posts/migrations_template.png)

We can now change this new file to execute the commands. For example:

[![main](/images/posts/migrations_migration.png)](/images/posts/migrations_migration.png)

To perform the migrations you need to:

    go run cmd/migrations/main.go up

And to undo the migration:

    go run cmd/migrations/main.go down

When we execute the **up** command, the collection **migrations** is checked to see if the last migration was successful. Automatically the ones that are still pending are executed, in this case **20190123090741_alter-user-data.go** and the collection is updated. This is the command that is executed during the deploy process of a new version of the application.

The **down** command does the inverse process by executing the migration logic and removing it from the collection.

We can access the code for these examples in this repository:

https://github.com/eminetto/clean-architecture-go

With this solution, we can improve our productivity because we have more experience in Go than in node.js . In addition, we can reuse code created in the project, such as Clean Architecture’s Use Cases. We can even create unit tests for migrations, which should be a next step in our implementation.

If you use the double Go + MongoDB I believe this solution may be useful and I hope I have helped.
