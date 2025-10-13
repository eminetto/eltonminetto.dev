---
title: "Object Calisthenics in Golang"
subtitle: ""
date: "2019-06-01T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

[Jeff Bay](http://www.xpteam.com/jeff/) introduced the term **Object Calisthenics** in his book [Thought Works Anthology] (https://pragprog.com/book/twa/thoughtworks-anthology). It is a set of good practices and programming rules that can improve the quality of our code.

<!--more-->

I saw these techniques for the first time when [Rafael Dohms](https://twitter.com/rdohms) and [Guilherme Blanco](https://twitter.com/guilhermeblanco) did a great job adapting them from the Java language to PHP. If you write code in PHP and do not know Object Calisthenics, I recommend two of the presentations made by them:

[Your code sucks, let's fix it](https://www.slideshare.net/rdohms/your-code-sucks-lets-fix-it-15471808)

[Object Calisthenics Applied to PHP](https://www.slideshare.net/guilhermeblanco/object-calisthenics-applied-to-php)

But what are these rules, anyway? They are, in the original version:

- One level of indentation per method.
- Don't use the ELSE keyword.
- Wrap all primitives and Strings in classes.
- First class collections.
- One dot per line.
- Don't abbreviate.
- Keep all classes less than 50 lines.
- No classes with more than two instance variables.
- No getters or setters.

As I mentioned earlier, Jeff created these rules based on the Java language and they need adaptations for other environments. Just as Rafael and Guilherme did for PHP, we need to look at each item and analyze it to see if it makes sense in Go.

The first point to consider is the name itself. _Calisthenics_ comes from the Greek and means a series of exercises to reach an end, such as improving your physical fitness. In this context, the exercises improve the conditioning of our code. The problem is the _Object_ term, because this concept does not exist in Go. Therefore, I propose a first change: from _Object Calisthenics_ to _Code Calisthenics_. I leave the comment area below to discuss if this is a good suggestion.

Let’s analyze the other items.

## One level of indentation per method

Applying this rule allows our code to be more readable.

Let’s apply the rule to the code:

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

One result, far more readable, would be:

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

The goal of this item is to avoid using the _else_ keyword, generating a cleaner and faster code because it has fewer execution flows.

Let's look at the code:

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

We can apply the _Early Return_ concept and remove the _else_ from the function _Login_:

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

This rule suggests that primitive types **that have behavior** must be encapsulated, in our case, in _structs_ or _types_ and not in _classes_. That way, we encapsulate the behavior logic and make the code easy to maintain. Let’s see the example:

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

Applying the rule, using _structs_:

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

Another possible and more idiomatic refactoring, using _types_ could be::

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

Besides being readable, the new code allows easy evolution of business rules and greater security in relation to what we are manipulating.

## First class collections

If you have a set of elements and want to manipulate them, create a dedicated structure for that collection. Thus, we can implement all the related behaviors to the collection in the structure, such as filters, validation rules, and so on.

So, the code:

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

Can be refactored to:

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

This rule states you should not chain functions, but use only those that are part of the same context.

Example:

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

We will refactor to:

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

This rule reinforces the use of [_"Law Of Demeter"_](http://wiki.c2.com/?LawOfDemeter):

> Only talk to your immediate friends

## Don't abbreviate

This rule does not apply directly to Go. The community has its own rules for creating variable names, including reasons for using smaller ones. I recommend reading this chapter of: [_‌Practical Go: Real world advice for writing maintainable Go programs_](https://dave.cheney.net/practical-go/presentations/qcon-china.html?utm_campaign=Golang%20Ninjas%20Newsletter&utm_medium=email&utm_source=Revue%20newsletter#_identifiers)

## Keep all classes less than 50 lines

Although there is no concept of _classes_ in Go, this rule states that entities should be small. We can adapt the idea to create small _structs_ and _interfaces_ that we can use, via composition, to form larger components. For instance, the _interface_:

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

Can be adapted to::

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

Thus, other interfaces and scenarios can reuse the _Reader_ and _Writer_.

## No classes with more than two instance variables

This rule does not seem to make sense in Go, but if you have any suggestions, please share.

## No Getters/Setters

Like the previous rule, this also does not seem to be adaptable to Go, because it is not a pattern used by the community, as seen in this [topic](https://golang.org/doc/effective_go.html#Getters) of Effective Go. But suggestions are welcome.

Applying these rules, among other good practices, it is possible to have a cleaner, simple and easy to maintain code. I would love to hear your opinions on the rules and these suggestions of adaptation to Go.

## References

[https://javflores.github.io/object-calisthenics/](https://javflores.github.io/object-calisthenics/)

[https://williamdurand.fr/2013/06/03/object-calisthenics/](https://williamdurand.fr/2013/06/03/object-calisthenics/)

[https://medium.com/web-engineering-vox/improving-code-quality-with-object-calisthenics-aa4ad67a61f1](https://medium.com/web-engineering-vox/improving-code-quality-with-object-calisthenics-aa4ad67a61f1)

I have adapted some examples used in this post from the repository: [https://github.com/rafos/object-calisthenics-in-go](https://github.com/rafos/object-calisthenics-in-go)

## Acknowledgment

Thank you Wagner Riffel and Francisco Oliveira for suggestions on how to improve the examples.
