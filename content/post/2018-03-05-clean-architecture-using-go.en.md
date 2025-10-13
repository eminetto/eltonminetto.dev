+++
title = "Clean Architecture using Golang"
subtitle = ""
date = "2018-03-05T10:54:24+02:00"
bigimg = ""
+++

# Update

I published an updated version of this post. Check it out at: [Clean Architecture, 2 years later](https://eltonminetto.net/en/post/2020-07-06-clean-architecture-2years-later/)

## What is Clean Architecture?

In his book “Clean Architecture: A Craftsman’s Guide to Software Structure and Design” famous author Robert “Uncle Bob” Martin presents an architecture with some important points like testability and independence of frameworks, databases and interfaces.

<!--more-->

The constraints in the Clean Architecture are :

- Independent of Frameworks. The architecture does not depend on the existence of some library of feature laden software. This allows you to use such frameworks as tools, rather than having to cram your system into their limited constraints.
- Testable. The business rules can be tested without the UI, Database, Web Server, or any other external element.
- Independent of UI. The UI can change easily, without changing the rest of the system. A Web UI could be replaced with a console UI, for example, without changing the business rules.
- Independent of Database. You can swap out Oracle or SQL Server, for Mongo, BigTable, CouchDB, or something else. Your business rules are not bound to the database.
- Independent of any external agency. In fact your business rules simply don’t know anything at all about the outside world.

More at [https://8thlight.com/blog/uncle-bob/2012/08/13/the-clean-architecture.html](https://8thlight.com/blog/uncle-bob/2012/08/13/the-clean-architecture.html)

So, based on this constraints, every layer must be independent and testable.

From Uncle Bob’s Architecture we can divide our code in 4 layers :


- Entities: encapsulate enterprise wide business rules. An entity in Go is a set of data structures and functions.
- Use Cases: the software in this layer contains application specific business rules. It encapsulates and implements all of the use cases of the system.
- Controller: the software in this layer is a set of adapters that convert data from the format most convenient for the use cases and entities, to the format most convenient for some external agency such as the Database or the Web
- Framework & Driver: this layer is generally composed of frameworks and tools such as the Database, the Web Framework, etc.

## Clean Architecture in Golang

Let’s use as an exemple the package user:

```
ls -ln pkg/user
-rw-r — r — 1 501 20 5078 Feb 16 09:58 entity.go
-rw-r — r — 1 501 20 3747 Feb 16 10:03 mongodb.go
-rw-r — r — 1 501 20 509 Feb 16 09:59 repository.go
-rw-r — r — 1 501 20 2403 Feb 16 10:30 service.go
```

In the file *entity.go* we have our entities:

[![ca-1](/images/posts/ca-1.png)](/images/posts/ca-1.png) 

In the file *repository.go* we have the interface that define a repository, where the entities will be stored. In this case the repository means the Framework & Driver layer in Uncle Bob architecture. His content is:

[![ca-2](/images/posts/ca-2.png)](/images/posts/ca-2.png) 

This interface can be implemented in any kind of storage layer, like MongoDB, MySQL, and so on. In our case we implemented using MongoDB, as seen in *mongodb.go*:

[![ca-3](/images/posts/ca-3.png)](/images/posts/ca-3.png) 


The file *service.go* represents the Use Case layer, as defined by Uncle Bob. In the file we have the interface Service and his implementation. The Service interface is:

[![ca-4](/images/posts/ca-4.png)](/images/posts/ca-4.png) 

The last layer, the Controller in our architecture is implemented in the content of api:

```
cd api ; tree
.
|____handler
| |____company.go
| |____user.go
| |____address.go
| |____skill.go
| |____invite.go
| |____position.go
|____rice-box.go
|____main.go

```


In the following code, from *api/main.go*, we can see how to use the services:

[![ca-5](/images/posts/ca-5.png)](/images/posts/ca-5.png) 

Now we can easily create tests to our packages, like:

[![ca-6](/images/posts/ca-6.png)](/images/posts/ca-6.png) 

Using the Clean Architecture we can change the database from MongoDB to Neo4j, for instance, without breaking the rest of application. And we can grow our software without losing quality and speed.

## References

https://hackernoon.com/golang-clean-archithecture-efd6d7c43047
https://8thlight.com/blog/uncle-bob/2012/08/13/the-clean-architecture.html
https://github.com/eminetto/clean-architecture-go