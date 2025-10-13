---
title: "Choosing dependencies using deps.dev"
date: 2023-04-19T08:30:43-03:00
draft: false
tags:
  - go
---

Choosing a project's dependencies is something we sometimes overlook, but it can have a very relevant impact. The following image illustrates the idea:

[![dependencies](/images/posts/dependecies.png)](/images/posts/dependecies.png)

To facilitate this process, Google recently launched a new project, [deps.dev](https://deps.dev). Its slogan summarizes its objective: _Understand your dependencies_. The tool supports programming languages such as JavaScript, Rust, Go, Python, and Java.

To show the advantages, imagine a scenario: a team is developing an API in Go and needs to choose a library to implement the [Circuit Breaker](https://martinfowler.com/bliki/CircuitBreaker.html) pattern. After some research on the internet and the excellent website [Awesome Go](https://awesome-go.com/), they reduced the list to the following options:

- [sony/gobreaker](https://github.com/sony/gobreaker)
- [mercari/go-circuitbreaker](https://github.com/mercari/go-circuitbreaker)
- [rubyist/circuitbreaker](https://github.com/rubyist/circuitbreaker)
- [afex/hystrix-go](https://github.com/afex/hystrix-go)

Let's search each in deps.dev to start the comparison. These are the links to the analysis of the libs:

- [sony/gobreaker](https://deps.dev/go/github.com%2Fsony%2Fgobreaker)
- [mercari/go-circuitbreaker](https://deps.dev/go/github.com%2Fmercari%2Fgo-circuitbreaker)
- [rubyist/circuitbreaker](https://deps.dev/go/github.com%2Frubyist%2Fcircuitbreaker)
- [afex/hystrix-go](https://deps.dev/go/github.com%2Fafex%2Fhystrix-go)

Some of the information presented stood out to me. For example, in the analysis of `gobreaker`:

- The tool creates a score for the lib, using criteria such as security, license, and whether it is actively maintained:

[![dependencies_score](/images/posts/dependencies_score.png)](/images/posts/dependencies_score.png)

- We can see how many dependencies the lib has and how many projects are using it, which can be a good sign of quality and trust from the community:

[![dependencies_dependents](/images/posts/dependencies_dependents.png)](/images/posts/dependencies_dependents.png)

It is also possible to see if the lib has any security warnings. The `mercari/go-circuitbreaker` lib presents a risk in this regard:

[![dependencies_security](/images/posts/dependencies_security.png)](/images/posts/dependencies_security.png)

With this information, the team can make a safer decision as to which libs they can use in their project.

Another handy feature is that deps.dev has an [API](https://docs.deps.dev/api/v3alpha/index.html). With this API, it is possible to create a check in the project's `Continuous Integration` service to verify if there are any security warnings related to dependencies or if there is a new version of an essential library.

deps.dev is a worthwhile project that can help teams choose and manage their project's dependencies.
