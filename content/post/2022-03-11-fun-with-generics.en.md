---
title: "Testing Generics in Go"
date: 2022-03-11T08:27:10-03:00
draft: false
tags:
  - go
---

It's finally (almost) among us!

Finally, after years of hearing that joke "what about Generics?" this long-awaited feature will be available in version 1.18 of the language, scheduled for release in March 2022.

In this post, I'll do an example using Generics and a small benchmark to check if there are any performance differences between a "regular" function and another using this new functionality.

To demonstrate this, I will use the library [lo](https://github.com/samber/lo), one of the first that uses Generics and that has recently gained prominence for implementing several valuable features for slices and maps.

The first step was to install Go 1.18, which at the time of writing this post is in the Release Candidate 1 version. For that, I followed this [documentation](https://groups.google.com/g/golang-announce/c/QHL1fTc352o/m/5sE6moURBwAJ?pli=1) and used the commands:

```
go install golang.org/dl/go1.18rc1@latest
go1.18rc1 download
```

These commands created the `sdk` directory in my user's home on macOS. We will use this directory to configure the IDE to recognize the new language version. I'm using Jetbrains' Goland, so my setup looks like this:

[![generics_goland](/images/posts/generics_goland.png)](/images/posts/generics_goland.png)

In addition to creating the `sdk` directory, the above commands created the `go1.18rc1` binary in the `go/bin` directory of my macOS user's home. It is this binary that we will use to run the tests:

```
eminetto@MacBook-Pro-da-Trybe ~/D/post-generics [1]> go1.18rc1 version
go version go1.18rc1 darwin/arm64
```

The next step was to create a directory and a `main.go` file:

```
mkdir post-generics
cd post-generics
go1.18rc1 mod init github.com/eminetto/post-generics
touch main.go
```

In `main.go` I wrote the following code:

```go
package main

import (
	"fmt"
)

func main() {
	s := []string{"Samuel", "Marc", "Samuel"}
	names := Uniq(s)
	fmt.Println(names)
	names = UniqGenerics(s)
	fmt.Println(names)
	i := []int{1, 20, 20, 10, 1}
	ids := UniqGenerics(i)
	fmt.Println(ids)
}

//from https://github.com/samber/lo/blob/master/slice.go
func UniqGenerics[T comparable](collection []T) []T {
	result := make([]T, 0, len(collection))
	seen := make(map[T]struct{}, len(collection))

	for _, item := range collection {
		if _, ok := seen[item]; ok {
			continue
		}

		seen[item] = struct{}{}
		result = append(result, item)
	}

	return result
}

func Uniq(collection []string) []string {
	result := make([]string, 0, len(collection))
	seen := make(map[string]struct{}, len(collection))

	for _, item := range collection {
		if _, ok := seen[item]; ok {
			continue
		}
		seen[item] = struct{}{}
		result = append(result, item)
	}

	return result
}


```

In the `main` function, it is possible to see the most significant advantage of Generics: we use the same code to remove duplicate entries in slices of strings and integers without changing the function.

When running the code, we can see the result:

```
eminetto@MacBook-Pro-da-Trybe ~/D/post-generics> go1.18rc1 run main.go
[Samuel Marc]
[Samuel Marc]
[1 20 10]
```

But what about performance? Are we missing something by adding this new functionality? To try to answer this, I did a little benchmark. The first step was to install the `faker` package to generate more data for the measure:

```
go1.18rc1 get -u github.com/bxcodec/faker/v3
```

And the code of `main_test.go` looks like this:

```go
package main

import (
	"github.com/bxcodec/faker/v3"
	"testing"
)

var names []string

func BenchmarkMain(m *testing.B) {
	for i := 0; i < 1000; i++ {
		names = append(names, faker.FirstName())
	}
}

func BenchmarkUniq(b *testing.B) {
	_ = Uniq(names)
}

func BenchmarkGenericsUniq(b *testing.B) {
	_ = UniqGenerics(names)
}
```

Running the benchmark, we can see the result:

```
eminetto@MacBook-Pro-da-Trybe ~/D/post-generics> go1.18rc1 test -bench=. -benchtime=100x
goos: darwin
goarch: arm64
pkg: github.com/eminetto/post-generics
BenchmarkMain-8                      100               482.1 ns/op
BenchmarkUniq-8                      100              1225 ns/op
BenchmarkGenericsUniq-8              100              1142 ns/op
PASS
ok      github.com/eminetto/post-generics       0.210s
```

I ran the benchmark several times, and in most cases, the version made with Generics was more performant, although the difference was not that big.

## Conclusion

This post is not an advanced study with scientifically proven benchmarks. It is just a basic test, so I recommend you consult more sources before making a final decision. Still, the first impression is that we are gaining an essential feature without any noticeable performance loss.

I believe I will wait for the final version of this feature to be more mature, probably after 1.18.x, to put it into production. Still, I see a significant evolution in Go applications in the coming months.

The excitement is starting to increase :)
