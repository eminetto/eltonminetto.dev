---
title: "Golang: using build tags to store configurations"
subtitle: ""
date: "2018-06-25T08:54:24+02:00"
bigimg: ""
tags:
  - go
---

One of the [12 factor](http://12factor.net), a methodology for building software-as-a-service apps, is:

> Store config in the environment

<!--more-->

This is really a good practice, but to achieve this you need to have control over the environment where your app will run. Although this is easy if we are developing an API or microservice the same can’t be said if our app is a CLI that will run in other environments.

In this post, I will show how to solve this using build tags, that are conditionals passed to Go compiler.

As the first step was to created a package called config:

[![bt-1](/images/posts/bt-1.png)](/images/posts/bt-1.png)

In each file, I used a build tag related to the different environments, like:

[![bt-2](/images/posts/bt-2.png)](/images/posts/bt-2.png)

The important part is the first line, that set the build tag:

    // +build dev

Now, we can use the tag in the go build command (or in go test, go run, and so on):

    go build -tags dev -o ./bin/api api/main.go
    go build -tags dev -o ./bin/search cmd/main.go

The compiler ignores files that have different tags and will use the others, so we do not need to change anything else in our project.

To use the configuration we just import the config package and use it:

[![bt-3](/images/posts/bt-3.png)](/images/posts/bt-3.png)

Using build tags, along with automation tools like make and the excellent [GoReleaser](https://goreleaser.com/) we can greatly ease the process of building and deploying applications written in Go.

If you want to read more about build tags a good start is the [official documentation](https://golang.org/pkg/go/build/). And if you want to see the full example the code is in [Github](https://github.com/eminetto/clean-architecture-go).
