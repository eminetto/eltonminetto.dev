---
title: "Interesting projects using WebAssembly"
date: 2024-04-06T08:00:43-03:00
draft: false
---
This text is the last part of a series of posts I wrote about one of the technologies that have had the most impact on me in recent years: WebAssembly. In the [first text](https://eltonminetto.dev/en/post/2023-11-17-webassembly-using-go-code-in-the-browser/), I discussed how to port Go code to run it in a web browser. In the [second part](https://eltonminetto.dev/en/post/2023-12-11-running-webassembly-in-go/), I showed how to use WebAssembly code in a Go project. In this post, I will discuss some exciting projects using this technology.

## Kong Gateway

Kong is an API Gateway used by companies around the world. It has always relied on "plugins" and "filters" that allow users to create new functionalities and add them to routes and services. For users to make these extensions, it was necessary to use the Lua scripting language (NOTE: it is possible to create plugins in Go in versions below 3.4, but unlike Lua, when using Go, performance is lower because Kong executes the logic in a separate process). From [version 3.4](https://konghq.com/blog/product-releases/gateway-3-4-oss) onwards, the team added the option to use WebAssembly. In [this post](https://konghq.com/blog/product-releases/webassembly-in-kong-gateway-3-4), you can learn more about the details and how to implement a simple filter using TinyGo. You can find a [template](https://github.com/Kong/proxy-wasm-go-filter-template) and a filter example to implement [rate limit](https://github.com/Kong/proxy-wasm-go-rate-limiting) in the project repository.

## Spin and SpinKube

The following two examples are open-source projects maintained by [Fermyon](https://www.fermyon.com/about) with contributions from companies like Microsoft and SUSE. The first is [Spin](https://www.fermyon.com/spin), which allows us to use WebAssembly to create Serverless applications. The second, SpinKube, combines some of the topics I'm most excited about these days: WebAssembly and Kubernetes Operators :) The [official website](https://www.spinkube.dev/) says, "By running applications in the Wasm abstraction layer, SpinKube offers developers a more powerful, efficient, and scalable way to optimize application delivery on Kubernetes." By the way, [this post](https://dev.to/thangchung/spinkube-the-first-look-at-webassemblywasi-application-spinapp-on-kubernetes-36jd) shows how to integrate SpinKube with [Dapr](https://dapr.io/), another technology I'm very interested in, and I should write some posts soon.

## wasmCloud

Another project that aims to facilitate the deployment and execution of WebAssembly applications is [wasmCloud](https://wasmcloud.com/): "wasmCloud is a universal application platform that helps you build and run globally distributed WebAssembly applications on any cloud and at any edge.". I wonder how long it will take until big players like AWS, Google Cloud, and Azure buy or implement similar solutions in their portfolios…

## Tarmac

Tarmac is a framework that facilitates the creation of WebAssembly applications. According to its [official website](https://tarmac.gitbook.io/tarmac-framework), "Framework for writing functions, microservices or monoliths with Web Assembly. Tarmac is language-agnostic and offers built-in support for key/value stores like BoltDB, Redis, and Cassandra, traditional SQL databases like MySQL and Postgres, and core features like mTLS authentication and observability." It is a project worth analyzing as it can speed up the implementation of applications you can host in one of the products I mentioned above.

## Onyx

Onyx is entirely different from previous examples as it is a new programming language focused on WebAssembly. According to the [documentation](https://wasmer.io/posts/onyxlang-powered-by-wasmer), "Onyx is a new programming language that features modern, expressive syntax, rigorous type safety, lightning-fast build times, and out-of-the-box cross-platform support thanks to WebAssembly." Developed for more than three years, it is a complete language with syntax similar to Go, some functional language features, a package manager, and support in major IDEs.

## Conclusions

The objective of this post was to try to leave you with a level of excitement close to mine :) I see great potential for the technology and believe it is worth the investment in studies as it should gain more prominence in the coming years, especially in scenarios close to infrastructure and backend.

What are your opinions on WebAssembly? Is it just more hype, or does it have the potential to be "disruptive"? Do you know some more examples of exciting applications? Contribute in the comments.