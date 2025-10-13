---
title: "Object Calisthenics em Golang"
subtitle: ""
date: "2019-06-01T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

O termo **Object Calisthenics** foi introduzido por [Jeff Bay](http://www.xpteam.com/jeff/) e publicado no livro [Thought Works Anthology](https://pragprog.com/book/twa/thoughtworks-anthology). Trata-se de um conjunto de boas práticas e regras de programação que podem ser aplicadas para melhorar a qualidade do código.

<!--more-->

Eu fui apresentado a estas técnicas quando o [Rafael Dohms](https://twitter.com/rdohms) e o [Guilherme Blanco](https://twitter.com/guilhermeblanco) fizeram um excelente trabalho adaptando-as da linguagem Java para PHP. Se você escreve código em PHP e ainda não conhece Object Calisthenics eu recomendo duas das apresentações feitas por eles:

[Your code sucks, let's fix it](https://www.slideshare.net/rdohms/your-code-sucks-lets-fix-it-15471808)

[Object Calisthenics Applied to PHP](https://www.slideshare.net/guilhermeblanco/object-calisthenics-applied-to-php)

Mas afinal, quais são estas regras? São elas, na versão original:

- One level of indentation per method.
- Don't use the ELSE keyword.
- Wrap all primitives and Strings in classes.
- First class collections.
- One dot per line.
- Don't abbreviate.
- Keep all classes less than 50 lines.
- No classes with more than two instance variables.
- No getters or setters.

Como comentei anteriormente, elas foram inicialmente criadas tendo como base a linguagem Java e adaptações são necessárias para outros ambientes. Assim como Rafael e Guilherme fizeram para PHP, é preciso olhar para cada item e analisá-lo para vermos se faz sentido em Go.

O primeiro ponto a considerar é o próprio nome. _Calisthenics_ vem do grego e significa uma série de exercícios para atingir um fim, como melhorar o seu condicionamento físico. Neste contexto os exercícios servem para melhorar o condicionamento do nosso código. O porém é o termo _Object_ pois este conceito não existe em Go. Por isso, proponho uma primeira mudança: de _Object Calisthenics_ para _Code Calisthenics_. Deixo o espaço de comentários para discutirmos se essa é uma boa sugestão ou não.

Vamos aos demais itens.

## One level of indentation per method

Aplicar esta regra permite que o nosso código seja mais legível.

Vamos aplicar a regra ao código:

```go
package chess

import "bytes"

type board struct {
	data [][]string
}

func NewBoard(data [][]string) *board {
	return &board{data: data}
}

func (b *board) Board() string {
	var buffer = &bytes.Buffer{}

	// level 0
	for i := 0; i < 10; i++ {
		// level 1
		for j := 0; j < 10; j++ {
			// level 2
			buffer.WriteString(b.data[i][j])
		}
		buffer.WriteString("\n")
	}

	return buffer.String()
}
```

Um possível resultado, bem mais legível, seria:

```go
package chess

import "bytes"

type board struct {
	data [][]string
}

func NewBoard(data [][]string) *board {
	return &board{data: data}
}

func (b *board) Board() string {
	var buffer = &bytes.Buffer{}

	b.collectRows(buffer)

	return buffer.String()
}

func (b *board) collectRows(buffer *bytes.Buffer) {
	for i := 0; i < 10; i++ {
		b.collectRow(buffer, i)
	}
}

func (b *board) collectRow(buffer *bytes.Buffer, row int) {
	for j := 0; j < 10; j++ {
		buffer.WriteString(b.data[row][j])
	}

	buffer.WriteString("\n")
}
```

## Don't use the ELSE keyword

A ideia deste item é evitarmos o uso da palavra chave _else_, gerando um código limpo e mais rápido, pois tem menos fluxos de execução.

Vejamos o código:

```go
package login

import (
	"github.com/user/project/user/repository"
)

type loginService struct {
	userRepository *repository.UserRepository
}

func NewLoginService() *loginService {
	return &loginService{
		userRepository: repository.NewUserRepository(),
	}
}

func (l *loginService) Login(userName, password string) {
	if l.userRepository.IsValid(userName, password) {
		redirect("homepage")
	} else {
		addFlash("error", "Bad credentials")

		redirect("login")
	}
}

func redirect(page string) {
	// redirect to given page
}

func addFlash(msgType, msg string) {
	// create flash message
}
```

Podemos aplicar o conceito _‌Early Return_ e remover o _else_ da função _Login_:

```go

func (l *loginService) Login(userName, password string) {
	if l.userRepository.IsValid(userName, password) {
		redirect("homepage")
		return
	}
	addFlash("error", "Bad credentials")
	redirect("login")
}
```

## Wrap all primitives and Strings in classes

Esta regra sugere que os tipos primitivos **que possuem comportamento** devem ser encapsulados, no nosso caso, em _structs_ ou _types_ e não em _classes_. Desta forma, a lógica do comportamento fica encapsulado e de fácil manutenção. Vamos ver o exemplo:

```go
package ecommerce

type order struct {
	pid int64
	cid int64
}

func CreateOrder(pid int64, cid int64) order {
	return order{
		pid: pid, cid: cid,
	}
}

func (o order) Submit() (int64, error) {
	// do some logic

	return int64(3252345234), nil
}
```

Aplicando a regra, usando _structs_:

```go
package ecommerce

import (
	"strconv"
)

type order struct {
	pid productID
	cid customerID
}

type productID struct {
	id int64
}

// some methods on productID struct

type customerID struct {
	id int64
}

// some methods on customerID struct

type orderID struct {
	id int64
}

func (oid orderID) String() string {
	return strconv.FormatInt(oid.id, 10)
}

// some other methods on orderID struct

func CreateOrder(pid int64, cid int64) order {
	return order{
		pid: productID{pid}, cid: customerID{cid},
	}
}

func (o order) Submit() (orderID, error) {
	// do some logic

	return orderId{int64(3252345234)}, nil
}
```

Outra possível refatoração, mais idiomática, usando _types_ poderia ser:

```go
package ecommerce

import (
	"strconv"
)

type order struct {
	pid productID
	cid customerID
}

type productID int64


// some methods on productID type

type customerID int64

// some methods on customerID type

type orderID int64

func (oid orderID) String() string {
	return strconv.FormatInt(int64(oid), 10)
}

// some other methods on orderID type

func CreateOrder(pid int64, cid int64) order {
	return order{
		pid: productID(pid), cid: customerID(cid),
	}
}

func (o order) Submit() (orderID, error) {
	// do some logic

	return orderID(int64(3252345234)), nil
}

```

Além de ficar mais legível o código alterado permite fácil evolução das regras de negócio e maior segurança em relação ao que estamos manipulando.

## First class collections

Se você tiver um conjunto de elementos e quiser manipulá-los, crie uma estrutura dedicada para essa coleção. Assim comportamentos relacionados à coleção serão implementados por sua própria estrutura como filtros, uma regra a cada elemento e etc.

Dado o código:

```go
package contact

import "fmt"

type person struct {
	name    string
	friends []string
}

type friend struct {
	name string
}

func NewPerson(name string) *person {
	return &person{
		name:    name,
		friends: []string{},
	}
}

func (p *person) AddFriend(name string) {
	p.friends = append(p.friends, name)
}

func (p *person) RemoveFriend(name string) {
	new := []string{}
	for _, friend := range p.friends {
		if friend != name {
			new = append(new, friend)
		}
	}
	p.friends = new
}

func (p *person) GetFriends() []string {
	return p.friends
}

func (p *person) String() string {
	return fmt.Sprintf("%s %v", p.name, p.friends)
}
```

Pode ser refatorado para:

```go
package contact

import (
	"strings"
	"fmt"
)

type friends struct {
	data []string
}

type person struct {
	name    string
	friends *friends
}

func NewFriends() *friends {
	return &friends{
		data: []string{},
	}
}

func (f *friends) Add(name string) {
	f.data = append(f.data, name)
}

func (f *friends) Remove(name string) {
	new := []string{}
	for _, friend := range f.data {
		if friend != name {
			new = append(new, friend)
		}
	}
	f.data = new
}

func (f *friends) String() string {
	return strings.Join(f.data, " ")
}

func NewPerson(name string) *person {
	return &person{
		name:    name,
		friends: NewFriends(),
	}
}

func (p *person) AddFriend(name string) {
	p.friends.Add(name)
}

func (p *person) RemoveFriend(name string) {
	p.friends.Remove(name)
}

func (p *person) GetFriends() *friends {
	return p.friends
}

func (p *person) String() string {
	return fmt.Sprintf("%s [%v]", p.name, p.friends)
}
```

## One dot per line

Essa regra cita que você não deve encadear funções e sim usar as que fazem parte do mesmo contexto.

Exemplo:

```go
package chess

type piece struct {
    representation string
}

type location struct {
	current *piece
}

type board struct {
    locations []*location
}

func NewLocation(piece *piece) *location {
	return &location{current: piece}
}

func NewPiece(representation string) *piece {
    return &piece{representation: representation}
}

func NewBoard() *board {
    locations := []*location{
        NewLocation(NewPiece("London")),
        NewLocation(NewPiece("New York")),
        NewLocation(NewPiece("Dubai")),
    }
    return &board{
        locations: locations,
    }
}

func (b *board) squares() []*location {
    return b.locations
}

func (b *board) BoardRepresentation() string {
    var buffer = &bytes.Buffer{}
    for _, l := range b.squares() {
        buffer.WriteString(l.current.representation[0:1])
    }
    return buffer.String()
}
```

Neste caso vamos refatorar para:

```go
package chess

import "bytes"

type piece struct {
    representation string
}

type location struct {
    current *piece
}

type board struct {
    locations []*location
}

func NewPiece(representation string) *piece {
    return &piece{representation: representation}
}

func (p *piece) character() string {
    return p.representation[0:1]
}

func (p *piece) addTo(buffer *bytes.Buffer) {
    buffer.WriteString(p.character())
}

func NewLocation(piece *piece) *location {
    return &location{current: piece}
}

func (l *location) addTo(buffer *bytes.Buffer) {
    l.current.addTo(buffer)
}

func NewBoard() *board {
    locations := []*location{
        NewLocation(NewPiece("London")),
        NewLocation(NewPiece("New York")),
        NewLocation(NewPiece("Dubai")),
    }
    return &board{
        locations: locations,
    }
}

func (b *board) squares() []*location {
    return b.locations
}

func (b *board) BoardRepresentation() string {
    var buffer = &bytes.Buffer{}
    for _, l := range b.squares() {
        l.addTo(buffer)
    }
    return buffer.String()
}

```

Esta regra reforça o uso da [_"Lei de Demeter"_](http://wiki.c2.com/?LawOfDemeter):

> Converse apenas com seus amigos imediatos

## Don't abbreviate

Esta regra é uma das que não se aplica diretamente a Go. A comunidade tem suas próprias regras para a criação de nomes de variáveis, inclusive razões por usarmos nomes menores. Recomendo a leitura deste capítulo do ótimo [_‌Practical Go: Real world advice for writing maintainable Go programs_](https://dave.cheney.net/practical-go/presentations/qcon-china.html?utm_campaign=Golang%20Ninjas%20Newsletter&utm_medium=email&utm_source=Revue%20newsletter#_identifiers)

## Keep all classes less than 50 lines

Apesar de não existir o conceito de _classes_ em Go, a ideia desta regra é que as entidades sejam pequenas. Podemos adaptar a ideia para criarmos _structs_ e _interfaces_ pequenas e que podem ser usadas, via composição, para formar componentes maiores. Por exemplo, a _interface_:

```go
type Repository interface {
	Find(id entity.ID) (*entity.User, error)
	FindByEmail(email string) (*entity.User, error)
	FindByChangePasswordHash(hash string) (*entity.User, error)
	FindByValidationHash(hash string) (*entity.User, error)
	FindByChallengeSubmissionHash(hash string) (*entity.User, error)
	FindByNickname(nickname string) (*entity.User, error)
	FindAll() ([]*entity.User, error)
	Update(user *entity.User) error
	Store(user *entity.User) (entity.ID, error)
	Remove(id entity.ID) error
}
```

Pode ser refatorada para:

```go
type Reader interface {
	Find(id entity.ID) (*entity.User, error)
	FindByEmail(email string) (*entity.User, error)
	FindByChangePasswordHash(hash string) (*entity.User, error)
	FindByValidationHash(hash string) (*entity.User, error)
	FindByChallengeSubmissionHash(hash string) (*entity.User, error)
	FindByNickname(nickname string) (*entity.User, error)
	FindAll() ([]*entity.User, error)
}

type Writer interface {
	Update(user *entity.User) error
	Store(user *entity.User) (entity.ID, error)
	Remove(id entity.ID) error
}

type Repository interface {
	Reader
	Writer
}
```

Desta forma, as interfaces _Reader_ e _Writer_ podem ser reutilizadas por outras interfaces e cenários.

## No classes with more than two instance variables

Esta regra não parece fazer sentido em Go, mas se tiver alguma sugestão por favor compartilhe.

## No Getters/Setters

Assim como a regra anterior, esta também não parece ser adaptável a Go pois não é um costume da comunidade usar este recurso, como pode ser visto neste tópico do [Efetive Go](https://golang.org/doc/effective_go.html#Getters). Mas sugestões de adaptação são bem-vindas.

Aplicando estas regras, entre outras boas práticas, é possível termos um código mais limpo, simples, performático e fácil de manter. Adoraria saber suas opiniões sobre as regras e desta sugestão de adaptação para a linguagem.

## Referências

[https://javflores.github.io/object-calisthenics/](https://javflores.github.io/object-calisthenics/)

[https://williamdurand.fr/2013/06/03/object-calisthenics/](https://williamdurand.fr/2013/06/03/object-calisthenics/)

[https://medium.com/web-engineering-vox/improving-code-quality-with-object-calisthenics-aa4ad67a61f1](https://medium.com/web-engineering-vox/improving-code-quality-with-object-calisthenics-aa4ad67a61f1)

Alguns exemplos usados neste post foram adaptados a partir do repositório: [https://github.com/rafos/object-calisthenics-in-go](https://github.com/rafos/object-calisthenics-in-go)

## Agradecimentos

Obrigado Wagner Riffel e Francisco Oliveira pelas sugestões de melhorias quanto aos exemplos.
