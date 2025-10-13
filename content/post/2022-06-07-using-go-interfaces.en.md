---
title: "Using Golang stdlib interfaces"
date: 2022-06-07T20:03:34-03:00
draft: false
tags:
  - go
---

In this post, I'll show you how to use two of the most exciting features of the Go language: its standard library (the stdlib in the title) and its interfaces.

Go is famous for providing a lot of functionality, thanks to its powerful standard library. Covering everything from text and JSON conversions to databases and HTTP servers, we can develop complex applications without importing third-party packages.

Another essential feature of the language is the power of its interfaces. Unlike object-oriented languages, Go does not have the `extends` keyword and allows us to implement an interface using a variable, struct, slice, etc. Just implement the identical function signatures defined in the interface, and that's it.

Let's use these two features to improve our application code.

## Implementing the error interface

The first interface we are going to explore is `error`:

```go
type error interface {
	Error() string
}
```

We can use any structure or variable that implements this interface as an error in functions and tests:

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

I created the struct `MyError` in this example and implemented the `Error` function, as defined by the interface. The struct now can be returned as an error in the `divide` function. We can create custom errors for our applications with extra information, logs, and other features thanks to this feature.

## Implementing the fmt. Stringer and fmt.Formatter interfaces

For the next example, I created a type called `Level,` which is an `int.` We can use this type in a library that generates application logs, and the fact that it is an integer allows us to do logic like `if os.Getenv('ENV') == "prod" && level < INFO` to control which messages should be processed or not.

But although it is convenient to use this type of logic like the one described above, it can be helpful to convert this value to a `string` in some scenarios. So this is what we are going to do by implementing the `fmt.Stringer` and `fmt.Formatter` interfaces:

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

Our example code is:

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

The `String()` function is used by the `fmt.Println(l)` function and also by the `fmt.Printf` function. In this example, I implement the `Format` function to demonstrate how we can create special formatting, in this case, `%l,` which I defined as being responsible for transforming the value into lowercase.

## Implementing the json.Marshaler interface

Let's now create a new struct, `Log,` which contains a `Level`:

```go
type Log struct {
	Message string `json:"message"`
	Level   Level  `json:"level"`
}
```

A common feature in a log package is converting the data to JSON:

```go
log := Log{
		Message: "Message log",
		Level:   ERROR,
}
j, _ := json.Marshal(log)
fmt.Println(string(j))
```

But the result is not exactly as expected, as the code generates `Level` as an integer:

```json
{ "message": "Message log", "level": 6 }
```

To quickly solve this, we can implement the `json.Marshaler` interface:

```go
type Marshaler interface {
    MarshalJSON() ([]byte, error)
}
```

The implementation looked like this:

```go
func (l Level) MarshalJSON() ([]byte, error) {
	buffer := bytes.NewBufferString(`"`)
	buffer.WriteString(toString[l])
	buffer.WriteString(`"`)
	return buffer.Bytes(), nil
}
```

And now the print result is as we expected:

```json
{ "message": "Message log", "level": "ERROR" }
```

## Implementing the sort.Interface interface

For the following example, we will order a `slice` of `structs,` a logic that appears in many scenarios. But, first, let's create the data that we will sort:

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

Let's now sort our slice, first in order of release. For this, we need to implement the `sort.Interface` interface:

```go
type Interface interface {
	Len() int
	Less(i, j int) bool
	Swap(i, j int)
}
```

For that, I added the following code snippet:

```go
type byReleaseDate []*Movie

func (e byReleaseDate) Len() int           { return len(e) }
func (e byReleaseDate) Swap(i, j int)      { e[i], e[j] = e[j], e[i] }
func (e byReleaseDate) Less(i, j int) bool { return e[i].ReleaseYear < e[j].ReleaseYear }

```

And in the `main` function, before the loop that prints the movies:

```go
sort.Sort(byReleaseDate(movies))
```

We can do the same with other cases. The following code is the complete example, with more than one sort and the implementation of the `fmt.Stringer` interface to facilitate the printing of movies:

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

The execution result was:

```
Pulp Fiction was released at 1994
Matrix was released at 1999
The Northman was released at 2022
====
Matrix was released at 1999
Pulp Fiction was released at 1994
The Northman was released at 2022
```

## And more...

Besides the examples I've shown here, perhaps the best known is the implementation of the `http.Handler` interface to develop Rest APIs. The interface:

```go
type Handler interface {
	ServeHTTP(ResponseWriter, *Request)
}
```

And the most straightforward implementation:

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

But as this example is very well known, I will not delve into it.

Go's stdlib has many packages and [interfaces](https://sweetohm.net/article/go-interfaces.en.html) that we can implement and extend to develop complex applications. I recommend researching the [documentation](https://pkg.go.dev) to find more interesting and valuable features.
