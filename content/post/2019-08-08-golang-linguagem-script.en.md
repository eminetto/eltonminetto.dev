---
title: "Using Golang as a scripting language"
subtitle: ""
date: "2019-08-08T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

Among the technical decisions we made during the development of [Codenation] (https://codenation.dev), one of the right ones was to choose Go as the main language.

<!--more-->

Thanks to this choice, and the adoption of [Clean Architecture](https://eltonminetto.net/en/post/2018-03-05-clean-architecture-using-go/), we gained productivity by using the same language for various tasks such as:

- our API server
- lambda functions
- [migrations](https://eltonminetto.net/en/post/2019-01-23-migracao-de-dados-com-go-e-mongodb/)
- our CLI, which runs on clients machines
- Slack chatbot to automate internal tasks
- and the subject of this post: task automation scripts

Taking a quick look at our repository you can see how we are using the language for all these purposes:

[![go_codenation](/images/posts/go_codenation.png)](/images/posts/go_codenation.png)

In the `cmd` directory you can see some uses we make of Go as a scripting language. Some of them run as part of the team’s development workflow, such as `trello-github` which automates integration tasks between these two tools. Others run as scheduled tasks via `crontab`, such as `load-bi`. And others run as part of our [CI/CD](https://eltonminetto.net/en/post/2018-08-01-monorepo-drone/) server workflow, such as `migrations`.

Among the advantages of Go in this scenario, compared to shell scripts, I can mention:

- It is a simple and powerful language. We can use features like `go routines` to create complex scripts
- Thanks to clean architecture, we can reuse the same business layer used by the rest of the project
- It is possible to generate binaries that can run on any developer machine or servers. Not having dependencies on performing an automation task makes it much easier
- We can use it without going through the compilation process by running with `go run`. Here having the Go executable is a requirement, but the installation is simple and we can also automate this.

## Useful Packages

Here is a list of packages we used to create these small applications:

[**github.com/fatih/color**](https://github.com/fatih/color)

Color lets you use colorized outputs, which increases usability.

[![cli_go](/images/posts/cli_go.png)](/images/posts/cli_go.png)

[**github.com/schollz/progressbar**](https://github.com/schollz/progressbar)

In the image above you can see this package in operation. This package make easy to create progress bars, useful in processes that take time to execute.

[**github.com/jimlawless/whereami**](github.com/jimlawless/whereami)

This package is useful for generating error messages as it captures the file’s name, line, function, etc. For example:

```
File: whereami_example1.go  Function: main.main Line: 15
```

[**github.com/spf13/cobra**](github.com/spf13/cobra)

Cobra is probably the most widely used package for developing command line applications in Go. According to the documentation it is used in major projects like Kubernetes, Hugo, Docker, among others. With it you can create professional applications with input processing, options, documentation. We use it in our CLI, as in the example:

[![cobra](/images/posts/cobra.png)](/images/posts/cobra.png)

There are other packages and libraries that can help you develop command line applications to automate various tasks in your development workflow and serve other teams. Check the [Awesome Go](https://github.com/avelino/awesome-go#command-line) project to find several interesting options.

I hope these tips help inspire new uses of language in your projects.
