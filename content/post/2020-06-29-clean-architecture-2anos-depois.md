---
title: "Clean Architecture, 2 anos depois"
subtitle: ""
date: "2020-06-29T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

**UPDATE:** Este post é antigo e não reflete mais o que eu acredito ser uma estrutura ideial. Em 2023, o que estou usando e recomendando é o que meus colegas e eu descrevemos [neste post](https://medium.com/inside-picpay/organizando-um-projeto-e-convencionando-nomes-em-go-c18b3fa88ba0).

Em Fevereiro de 2018 escrevi aquele que viria a ser o mais relevante texto que já publiquei: [Clean Architecture using Golang](https://eltonminetto.dev/en/post/2018-03-05-clean-architecture-using-go/). Com mais de 105 mil views o assunto gerou apresentações em alguns eventos de Go e PHP, além de me proporcionar a oportunidade de conversar sobre o assunto com várias pessoas.

Conforme fomos usando esta arquitetura para o desenvolvimento dos produtos da [Codenation](https://codenation.dev) fomos ganhando experiência, resolvendo problemas e também gerando novos posts:

- [Golang: usando build tags para armazenar configurações](https://eltonminetto.dev/post/2018-06-25-golang-usando-build-tags/)
- [Integração contínua em projetos usando monorepo](https://eltonminetto.dev/post/2018-08-01-monorepo-drone/)
- [Monitorando uma aplicação Golang com o Supervisor](https://eltonminetto.dev/post/2018-11-28-monitorando-app-go-com-supervisor/)
- [Migração de dados com Golang e MongoDB](https://eltonminetto.dev/post/2019-01-23-migracao-de-dados-com-go-e-mongodb/)
- [Usando Golang como linguagem de script](https://eltonminetto.dev/post/2019-08-08-golang-linguagem-script/)
- [Criando mocks para testes usando GoMock](https://eltonminetto.dev/post/2019-12-19-usando-gomock/)
- [Usando Prometheus para coletar métricas de aplicações Golang](https://eltonminetto.dev/post/2020-03-12-golang-prometheus/)
- [Fazendo profiling de aplicações Golang usando pprof](https://eltonminetto.dev/post/2020-04-08-golang-pprof/)
- [Testando APIs em Golang usando apitest](https://eltonminetto.dev/post/2020-04-10-golang-apitest/)

Depois dessa experiência toda posso afirmar com certeza:

> Escolher a Clean Architecture foi a melhor decisão técnica que tomamos!

A segunda melhor foi a escolha da linguagem Go. Fiz uma palestra sobre essa escolha. Os [slides](https://speakerdeck.com/eminetto/por-que-e-como-usamos-go-na-codenation) e o [video](https://www.youtube.com/watch?v=Z-JQOCSdxdU) estão disponíveis caso queira ver mais detalhes.

Além de ressaltar o sucesso que tivemos com a Clean Architecture, este post serve para divulgar um [repositório](https://github.com/eminetto/clean-architecture-go-v2) que criei com uma nova versão do exemplo de implementação em Go. Ele é uma atualização com melhorias na organização dos códigos e diretórios, bem como é um exemplo mais completo para quem está querendo implementar esta arquitetura.

Abaixo faço uma explicação do que significa cada diretório do projeto.

## Camada Entity

Vamos começar pela camada mais interna da arquitetura.

De acordo com o [post do Uncle Bob](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html):

> Encapsulam as regras de negócios de toda a empresa. Uma entidade pode ser um objeto com métodos ou um conjunto de estruturas de dados e funções. Não importa, desde que as entidades possam ser usadas por muitos aplicativos diferentes na empresa.

A estrutura ficou desta forma:

[![entity](/images/posts/1-entity_book.png)](/images/posts/1-entity_book.png)

Neste pacote temos a definição das entidades do nosso negócio e seus respectivos testes unitários. Por exemplo, a entidade `user`:

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

## Camada UseCase

De acordo com o Uncle Bob:

> O software nesta camada contém regras de negócios específicas do aplicativo. Ele encapsula e implementa todos os casos de uso do sistema

A estrutura ficou desta forma:

[![domain](/images/posts/2-domain_loan.png)](/images/posts/2-domain_loan.png)

Nos pacotes dentro de `usecase` implementamos as demais regras de negócio do nosso produto.

Por exemplo, o arquivo `usecase\loan\service.go`:

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

Também encontramos os `mocks` gerados pelo `Gomock`, conforme explicado neste [post](https://eltonminetto.dev/post/2019-12-19-usando-gomock/). Estes mocks são usados pelas demais camadas da arquitetura durante os testes.

## Camada framework e driver

De acordo com o Uncle Bob:

> A camada mais externa geralmente é composta de estruturas e ferramentas como o Banco de Dados, a Estrutura da Web, etc. Esta camada é onde todos os detalhes vão.

[![driver](/images/posts/6-driver.png)](/images/posts/6-driver.png)

Por exemplo, no arquivo `infrastructure/repository/user_mysql.go` temos a implementação da interface `Repository` em MySQL. Se precisarmos alterar para outro banco, este é o local onde iríamos criar as novas implementações.

## Camada Interface Adapters

Os códigos nesta camada adaptam e convertem os dados do formato usado pelas entidades e use cases para agentes externos como bancos de dados , web, etc.

Nesta aplicação de exemplo existem duas formas de acesso aos `UseCases`. A primeira é através de uma `API` e a segunda é usando um aplicativo de linha de comando (`CLI`).

A estrutura do `CLI` é bem simples:

[![cli](/images/posts/4-cmd.png)](/images/posts/4-cmd.png)

Ele faz uso dos pacotes de domínio para realizar uma busca de livros:

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

No exemplo acima é possível ver o uso do pacote `config`. Sua estrutura pode ser vista abaixo e mais detalhes encontrados neste [post](https://eltonminetto.dev/post/2018-06-25-golang-usando-build-tags/).

[![config](/images/posts/3-config.png)](/images/posts/3-config.png)

A estrutura da `API` é um pouco mais complexa e composta por três pacotes: `handler`, `presenter` e `middleware`.

O pacote `handler` é responsável pelo tratamento das `requests` e `responses` `HTTP`, bem como usar as regras de negócio existentes nos `usecases`.

[![handler](/images/posts/5-handler.png)](/images/posts/5-handler.png)

Os `presenters` são responsáveis pela representação dos dados que serão gerados como `response` pelos `handlers`.

[![presenter](/images/posts/6-presenter.png)](/images/posts/6-presenter.png)

Desta forma, a entidade `User`:

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

Vai ser transformada em:

```go
type User struct {
	ID        entity.ID `json:"id"`
	Email     string    `json:"email"`
	FirstName string    `json:"first_name"`
	LastName  string    `json:"last_name"`
}
```

Com isso ganhamos maior controle em relação a como uma entidade será entregue pela `API`.

No último pacote da `API` encontramos os `middlewares`, que são usados por vários `endpoints`:

[![middlware](/images/posts/7-middleware.png)](/images/posts/7-middleware.png)

## Pacotes auxiliares

Além dos pacotes comentados acima, podemos incluir na nossa aplicação outros trechos de código que podem ser utilizados por várias camadas. São pacotes que fornecem funcionalidades comuns como criptografia, log, tratamento de arquivos, etc. Estas funcionalidades não fazem parte do domínio da nossa aplicação, e podem ser inclusive reutilizados por outros projetos:

[![pkg](/images/posts/8-pkg.png)](/images/posts/8-pkg.png)

No [README.md do repositório](https://github.com/eminetto/clean-architecture-go-v2) constam mais detalhes, como instruções para compilação e exemplos de uso.

Espero com este post fortalecer minha recomendação quanto a esta arquitetura e também receber feedbacks quanto aos códigos. Se você quer aprender a usar esta arquitetura em sua linguagem favorita, fica a sugestão para usar este repositório como exemplo para este aprendizado. Assim podemos ter diferentes implementações, em diferentes linguagens, para facilitar a comparação.

Agradecimentos especiais ao amigo [Gustavo Schirmer](https://twitter.com/hurrycaner) que deu ótimos feedbacks sobre o texto e os códigos.
