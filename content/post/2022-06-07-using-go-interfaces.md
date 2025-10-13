---
title: "Usando as interfaces da stdlib de Go"
date: 2022-06-07T20:03:34-03:00
draft: false
tags:
  - go
---

Neste post vou mostrar como usar duas das features mais interessantes da linguagem Go: sua biblioteca padrão (a stdlib do título) e interfaces.

Go é famosa por prover uma grande quantidade de funcionalidades nativamente, graças a sua biblioteca padrão poderosa. Cobrindo desde conversões de texto e json até bancos de dados e servidores HTTP, podemos desenvolver aplicações complexas sem a necessidade de importar pacotes de terceiros.

Outra característica importante da linguagem é o poder das suas interfaces. Diferente de linguagens orientadas a objetos, Go não possui a palavra-chave `extends` e permite que uma interface seja implementada por uma variável, struct, slice, etc. Basta que sejam implementadas as mesmas assinaturas de função definidas na interface e pronto.

Vamos usar estas duas features para incrementar o código de nossas aplicações.

## Implementando a interface error

A primeira interface que vamos explorar é a `error`:

```go
type error interface {
	Error() string
}
```

Qualquer estrutura ou variável que implementar essa interface pode ser reconhecida como um erro em funções e testes:

```go
package main

import (
	"fmt"
)

type MyError struct {
	Message string
}

func (m MyError) Error() string {
	return fmt.Sprintf("Message: %s", m.Message)
}

func main() {
	_, err := divide(10, 0)
	if err != nil {
		fmt.Println(err)
	}
}

func divide(x, y int) (float64, error) {
	if y <= 0 {
		return 0.0, MyError{
			Message: "error in divide function",
		}
	}
	return float64(x / y), nil
}

```

Neste exemplo criei a struct `MyError` e implementei a função `Error`, conforme a interface. Fazendo isso, a struct pode ser retornada como um erro na função `divide`. Graças a essa feature podemos criar erros customizados para nossas aplicações, com informações extras, logs e outras funcionalidades.

## Implementando as interfaces fmt.Stringer e fmt.Formatter

Para o próximo exemplo eu criei um tipo chamado `Level`, que é um `int`. Ele pode ser usado em uma biblioteca que gera logs de uma aplicação e o fato de ser um inteiro permite fazermos lógicas como `if os.Getenv('ENV') == "prod" && level < INFO` para controlarmos quais mensagens devem ser processadas ou não.

Mas apesar de ser bem prático para usarmos esse tipo em lógicas como a descrita acima, pode ser útil convertermos esse valor em uma `string` em alguns cenários. É o que vamos fazer implementando as interfaces `fmt.Stringer` e `fmt.Formatter`:

```go
type Stringer interface {
	String() string
}
```

```go
type Formatter interface {
	Format(f State, c rune)
}
```

O código do nosso exemplo é:

```go
package main

import (
	"fmt"
	"strings"
)

type Level int

const (
	DEBUG Level = iota + 1
	INFO
	NOTICE
	ALERT
	WARN
	ERROR
	CRITICAL
	FATAL
	DISASTER
)

var toString = map[Level]string{
	DEBUG:    "DEBUG",
	INFO:     "INFO",
	NOTICE:   "NOTICE",
	ALERT:    "ALERT",
	WARN:     "WARN",
	ERROR:    "ERROR",
	CRITICAL: "CRITICAL",
	FATAL:    "FATAL",
	DISASTER: "DISASTER",
}

func (l Level) String() string {
	return toString[l]
}

func (l Level) Format(f fmt.State, c rune) {
	switch c {
	case 'l':
		fmt.Fprint(f, strings.ToLower(toString[l]))
	default:
		fmt.Fprintf(f, toString[l])
	}
}
func main() {
	l := DEBUG
	fmt.Println(l)
	fmt.Printf("Level: %l\n", l)
}
```

A função `String()` é usada pela função `fmt.Println(l)` e também pela `	fmt.Printf`. Neste exemplo, a função `Format` foi implementada apenas para demonstrar como podemos criar formatações especiais, neste caso o `%l`, que eu defini como sendo responsável por transformar o valor em letras minúsculas.

## Implementando a interface json.Marshaler

Vamos agora criar uma nova struct, `Log`, que contém um `Level`:

```go
type Log struct {
	Message string `json:"message"`
	Level   Level  `json:"level"`
}
```

Um recurso comum em um pacote de logs é a conversão em JSON:

```go
log := Log{
		Message: "Message log",
		Level:   ERROR,
}
j, _ := json.Marshal(log)
fmt.Println(string(j))
```

Mas o resultado não é exatamente o esperado, pois o `Level` foi gerado como um inteiro:

```json
{ "message": "Message log", "level": 6 }
```

Para resolver isso de maneira fácil podemos implementar a interface `json.Marshaler`:

```go
type Marshaler interface {
    MarshalJSON() ([]byte, error)
}
```

A implementação ficou desta forma:

```go
func (l Level) MarshalJSON() ([]byte, error) {
	buffer := bytes.NewBufferString(`"`)
	buffer.WriteString(toString[l])
	buffer.WriteString(`"`)
	return buffer.Bytes(), nil
}
```

E agora o resultado da impressão ficou como esperávamos:

```json
{ "message": "Message log", "level": "ERROR" }
```

## Implementando a interface sort.Interface

Para o próximo exemplo vamos ordenar um `slice` de `structs`, uma lógica que aparece em vários cenários. Primeiro vamos criar os dados que serão ordenados:

```go
package main

import (
	"fmt"
)

type Movie struct {
	ReleaseYear int
	Title       string
}

func main() {
	movies := []*Movie{
		&Movie{
			ReleaseYear: 2022,
			Title:       "The Northman",
		},
		&Movie{
			ReleaseYear: 1994,
			Title:       "Pulp Fiction",
		},
		&Movie{
			ReleaseYear: 1999,
			Title:       "Matrix",
		},
	}
	for _, m := range movies {
		fmt.Println(m)
	}
}
```

Vamos agora ordenar o nosso slice, primeiro por ordem de lançamento. Para isso precisamos implementar a interface `sort.Interface`:

```go
type Interface interface {
	Len() int
	Less(i, j int) bool
	Swap(i, j int)
}
```

Para isso adicionei o seguinte trecho de código:

```go
type byReleaseDate []*Movie

func (e byReleaseDate) Len() int           { return len(e) }
func (e byReleaseDate) Swap(i, j int)      { e[i], e[j] = e[j], e[i] }
func (e byReleaseDate) Less(i, j int) bool { return e[i].ReleaseYear < e[j].ReleaseYear }

```

E na função `main`, antes do loop que faz a impressão dos filmes:

```go
sort.Sort(byReleaseDate(movies))
```

Podemos fazer o mesmo com outras ordenações. O código a seguir é o exemplo completo, com mais de uma ordenação e também a implementação da interface `fmt.Stringer` para facilitar a impressão dos filmes:

```go
package main

import (
	"fmt"
	"sort"
)

type Movie struct {
	ReleaseYear int
	Title       string
}

type byReleaseDate []*Movie

func (e byReleaseDate) Len() int           { return len(e) }
func (e byReleaseDate) Swap(i, j int)      { e[i], e[j] = e[j], e[i] }
func (e byReleaseDate) Less(i, j int) bool { return e[i].ReleaseYear < e[j].ReleaseYear }

type byTitle []*Movie

func (e byTitle) Len() int           { return len(e) }
func (e byTitle) Swap(i, j int)      { e[i], e[j] = e[j], e[i] }
func (e byTitle) Less(i, j int) bool { return e[i].Title < e[j].Title }

func (m Movie) String() string {
	return fmt.Sprintf("%s was released at %d", m.Title, m.ReleaseYear)
}

func main() {
	movies := []*Movie{
		&Movie{
			ReleaseYear: 2022,
			Title:       "The Northman",
		},
		&Movie{
			ReleaseYear: 1994,
			Title:       "Pulp Fiction",
		},
		&Movie{
			ReleaseYear: 1999,
			Title:       "Matrix",
		},
	}
	sort.Sort(byReleaseDate(movies))
	for _, m := range movies {
		fmt.Println(m)
	}
	fmt.Println("====")
	sort.Sort(byTitle(movies))
	for _, m := range movies {
		fmt.Println(m)
	}
}
```

O resultado da execução foi:

```
Pulp Fiction was released at 1994
Matrix was released at 1999
The Northman was released at 2022
====
Matrix was released at 1999
Pulp Fiction was released at 1994
The Northman was released at 2022
```

## E mais...

Além dos exemplos que mostrei aqui, talvez o mais conhecido é a implementação da interface `http.Handler`, que é usada para o desenvolvimento de APIs Rest. A interface:

```go
type Handler interface {
	ServeHTTP(ResponseWriter, *Request)
}
```

E a implementação mais simples:

```go
package main

import (
	"fmt"
	"net/http"
)

type helloHandler struct{}

func (h helloHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	fmt.Fprintf(w, "HeloWorld")
}

func main() {
	http.Handle("/hello", helloHandler{})
	http.ListenAndServe(":8090", nil)
}
```

Mas como esse exemplo é muito conhecido não vou me aprofundar nele.

A stdlib possui uma grande quantidade de pacotes e [interfaces](https://sweetohm.net/article/go-interfaces.en.html) que podem ser implementadas e extendidas para o desenvolvimento de aplicações complexas. Recomendo a investigação [na documentação](https://pkg.go.dev) para encontrar mais destas funcionalidades interessantes e úteis.
