+++
title = "Clean Architecture, 2 years later"
subtitle = ""
date = "2020-07-06T10:54:24+02:00"
bigimg = ""

+++

**UPDATE:** This post is old and no longer reflects what I believe to be an ideal structure for a project. In 2023, I am using and recommending what my colleagues and I have described in [this post](https://medium.com/inside-picpay/organizing-projects-and-defining-names-in-go-7f0eab45375d).

In February 2018 I wrote what would become the most relevant text I have ever published: [Clean Architecture using Golang](https://eltonminetto.dev/en/post/2018-03-05-clean-architecture-using-go/). With more than 105k views, the post generated presentations at some Go and PHP events and allowed me to talk about software architecture with several people. 

Using this architecture for the development of [Codenation](https://codenation.dev)'s products, we gained experience and solved problems. We wrote some posts reporting these experiences:

- [Golang: using build tags to store configurations](https://eltonminetto.dev/en/post/2018-06-25-golang-usando-build-tags/)
- [Continuous integration in projects using monorepo](https://eltonminetto.dev/en/post/2018-08-01-monorepo-drone/)
- [Monitoring a Golang application with Supervisor](https://eltonminetto.dev/en/post/2018-11-28-monitorando-app-go-com-supervisor/)
- [Data Migration with Golang and MongoDB](https://eltonminetto.dev/en/post/2019-01-23-migracao-de-dados-com-go-e-mongodb/)
- [Using Golang as a scripting language](https://eltonminetto.dev/en/post/2019-08-08-golang-linguagem-script/)
- [Creating test mocks using GoMock](https://eltonminetto.dev/en/post/2019-12-19-gomock/)
- [Using Prometheus to collect metrics from Golang applications](https://eltonminetto.dev/en/post/2020-03-13-golang-prometheus/)
- [Profiling Golang applications using pprof](https://eltonminetto.dev/en/post/2020-04-08-golang-pprof/)
- [Testing APIs in Golang using apitest](https://eltonminetto.dev/en/post/2020-04-21-golang-apitest/)

After this whole experience I can say::

> Choosing Clean Architecture was the best technical decision we made!

With this post, I want to share a [repository](https://github.com/eminetto/clean-architecture-go-v2) with a new example implementation in Go. It is an update with improvements in the organization of codes and directories, as well as a more complete example for those who are looking to implement this architecture.

In the next topics, I explain what each directory means.

## Entity layer

Let's start with the innermost layer of the architecture.

According to [Uncle Bob's post](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html):

> Entities encapsulate *Enterprise wide* business rules. An entity can be an object with methods, or it can be a set of data structures and functions. It doesn’t matter so long as the entities could be used by many different applications in the enterprise.

The structure looked like this:

[![entity](/images/posts/1-entity_book.png)](/images/posts/1-entity_book.png)

In this package we have the definition of our entities and their respective unit tests. For example, the entity `user`:

```go
package entity

import (
	"time"

	"golang.org/x/crypto/bcrypt"
)

//User data
type User struct {
	ID        ID
	Email     string
	Password  string
	FirstName string
	LastName  string
	CreatedAt time.Time
	UpdatedAt time.Time
	Books     []ID
}

//NewUser create a new user
func NewUser(email, password, firstName, lastName string) (*User, error) {
	u := &User{
		ID:        NewID(),
		Email:     email,
		FirstName: firstName,
		LastName:  lastName,
		CreatedAt: time.Now(),
	}
	pwd, err := generatePassword(password)
	if err != nil {
		return nil, err
	}
	u.Password = pwd
	err = u.Validate()
	if err != nil {
		return nil, ErrInvalidEntity
	}
	return u, nil
}

//AddBook add a book
func (u *User) AddBook(id ID) error {
	_, err := u.GetBook(id)
	if err == nil {
		return ErrBookAlreadyBorrowed
	}
	u.Books = append(u.Books, id)
	return nil
}

//RemoveBook remove a book
func (u *User) RemoveBook(id ID) error {
	for i, j := range u.Books {
		if j == id {
			u.Books = append(u.Books[:i], u.Books[i+1:]...)
			return nil
		}
	}
	return ErrNotFound
}

//GetBook get a book
func (u *User) GetBook(id ID) (ID, error) {
	for _, v := range u.Books {
		if v == id {
			return id, nil
		}
	}
	return id, ErrNotFound
}

//Validate validate data
func (u *User) Validate() error {
	if u.Email == "" || u.FirstName == "" || u.LastName == "" || u.Password == "" {
		return ErrInvalidEntity
	}

	return nil
}

//ValidatePassword validate user password
func (u *User) ValidatePassword(p string) error {
	err := bcrypt.CompareHashAndPassword([]byte(u.Password), []byte(p))
	if err != nil {
		return err
	}
	return nil
}

func generatePassword(raw string) (string, error) {
	hash, err := bcrypt.GenerateFromPassword([]byte(raw), 10)
	if err != nil {
		return "", err
	}
	return string(hash), nil
}
``` 


## Use Case Layer

According to Uncle Bob::

> The software in this layer contains application specific business rules. It encapsulates and implements all of the use cases of the system

The structure look like this:

[![domain](/images/posts/2-domain_loan.png)](/images/posts/2-domain_loan.png)

In packages within `usecase` we implement the other business rules of our product.

For example, the file `usecase\loan\service.go`:

```go
package loan

import (
	"github.com/eminetto/clean-architecture-go-v2/entity"
	"github.com/eminetto/clean-architecture-go-v2/usecase/book"
	"github.com/eminetto/clean-architecture-go-v2/usecase/user"
)

//Service loan usecase
type Service struct {
	userService user.UseCase
	bookService book.UseCase
}

//NewService create new use case
func NewService(u user.UseCase, b book.UseCase) *Service {
	return &Service{
		userService: u,
		bookService: b,
	}
}

//Borrow borrow a book to an user
func (s *Service) Borrow(u *entity.User, b *entity.Book) error {
	u, err := s.userService.GetUser(u.ID)
	if err != nil {
		return err
	}
	b, err = s.bookService.GetBook(b.ID)
	if err != nil {
		return err
	}
	if b.Quantity <= 0 {
		return entity.ErrNotEnoughBooks
	}

	err = u.AddBook(b.ID)
	if err != nil {
		return err
	}
	err = s.userService.UpdateUser(u)
	if err != nil {
		return err
	}
	b.Quantity--
	err = s.bookService.UpdateBook(b)
	if err != nil {
		return err
	}
	return nil
}

//Return return a book
func (s *Service) Return(b *entity.Book) error {
	b, err := s.bookService.GetBook(b.ID)
	if err != nil {
		return err
	}

	all, err := s.userService.ListUsers()
	if err != nil {
		return err
	}
	borrowed := false
	var borrowedBy entity.ID
	for _, u := range all {
		_, err := u.GetBook(b.ID)
		if err != nil {
			continue
		}
		borrowed = true
		borrowedBy = u.ID
		break
	}
	if !borrowed {
		return entity.ErrBookNotBorrowed
	}
	u, err := s.userService.GetUser(borrowedBy)
	if err != nil {
		return err
	}
	err = u.RemoveBook(b.ID)
	if err != nil {
		return err
	}
	err = s.userService.UpdateUser(u)
	if err != nil {
		return err
	}
	b.Quantity++
	err = s.bookService.UpdateBook(b)
	if err != nil {
		return err
	}

	return nil
}

```

We also found the `mocks` generated by `Gomock`, as explained in this [post](https://eltonminetto.dev/en/post/2019-12-19-gomock/). The other layers of the architecture will use this mocks during the tests.


## Frameworks and Drivers layer

According to Uncle Bob:

> The outermost layer is generally composed of frameworks and tools such as the Database, the Web Framework, etc.This layer is where all the details go.

[![driver](/images/posts/6-driver.png)](/images/posts/6-driver.png)

For example, in the file `infrastructure/repository/user_mysql.go` we have the implementation of the interface `Repository` in MySQL. If we need to change to another database, this is where we would create the new implementation.


## Interface Adapters layer

The codes in this layer adapt and convert the data to the format used by the entities and use cases for external agents such as databases, web, etc.

In this application, there are two ways to access the `UseCases`. The first is through an `API` and the second is using a command line application (`CLI`).

The `CLI`'s structure is very simple:

[![cli](/images/posts/4-cmd.png)](/images/posts/4-cmd.png)

It makes use of domain packages to perform a book search:

```go
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
for _, j := range all {
	fmt.Printf("%s %s \n", j.Title, j.Author)
}
```	

In the example above, you can see the use of the `config` package. You can see its structure below, and more details in this [post](https://eltonminetto.dev/post/2018-06-25-golang-usando-build-tags/). 

[![config](/images/posts/3-config.png)](/images/posts/3-config.png)

The `API` structure is more complex, with three packages: `handler`, `presenter`, and `middleware`.

The `handler` package handle `HTTP` `requests` and `responses`, as well as using existing business rules in the `usecases`.

[![handler](/images/posts/5-handler.png)](/images/posts/5-handler.png)

The `presenters` are responsible for formatting the data generated as a `response` by `handlers`.


[![presenter](/images/posts/6-presenter.png)](/images/posts/6-presenter.png)


In this way, the entity `User`:

```go
type User struct {
	ID        ID
	Email     string
	Password  string
	FirstName string
	LastName  string
	CreatedAt time.Time
	UpdatedAt time.Time
	Books     []ID
}
```


It can be transformed into:

```go
type User struct {
	ID        entity.ID `json:"id"`
	Email     string    `json:"email"`
	FirstName string    `json:"first_name"`
	LastName  string    `json:"last_name"`
}
```


This gives us control over how an entity will be delivered via the `API`.

In the last package of the `API` we find the `middlewares`, used by several `endpoints`:

[![middlware](/images/posts/7-middleware.png)](/images/posts/7-middleware.png)

## Support packages

They are packages that provide common functionality such as encryption, logging, file handling, etc. These features are not part of the domain of our application, and all the layers can use them. Even other applications can import and use these packages.

[![pkg](/images/posts/8-pkg.png)](/images/posts/8-pkg.png)

The [README.md](https://github.com/eminetto/clean-architecture-go-v2) contains more details, such as instructions for compilation and usage examples.

My goals with this post to strengthen my recommendation on this architecture and also to receive feedback about the codes. 

If you want to learn how to use this architecture in your favorite programming language, you could use this repository as an example of this learning. That way, we can have different implementations, in different languages, to ease the comparison.

Special thanks to my friend [Gustavo Schirmer](https://twitter.com/hurrycaner)  who gave great feedbacks on the text and the codes.