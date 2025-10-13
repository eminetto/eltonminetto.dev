---
title: Go is a platform
date: 2024-06-12T08:00:43-03:00
draft: false
---

Thanks to the Google Developer Experts program, I had the opportunity to participate in Google I/O in Mountain View, California, in May this year. Among the various talks I watched, one of my favorites was ‌ **Boost performance of Go applications with profile-guided optimization**, which you can watch on [Youtube](https://www.youtube.com/watch?v=FwzE5Sdhhdw).

Although Profile Guided Optimization (PGO) is one of the most exciting features of the language, what caught my attention the most was the first part of the talk, which [Cameron Balahan](https://www.linkedin.com/in/cameronbalahan/), a Group Product Manager at Google Cloud, presented.

The part that blew my mind was the statement:

[![platform](/images/posts/go_is_a_platform.png)](/images/posts/go_is_a_platform.png)

He starts by talking about the "DevOps Life Cycle":

[![sldc](/images/posts/sldc.png)](/images/posts/sldc.png)

And he goes on to highlight the qualities of Go in three crucial aspects:

- development speed;
- security;
- performance.


[![go_developer_velocity](/images/posts/go_developer_velocity.png)](/images/posts/go_developer_velocity.png)

My comments on each item mentioned in the images:

- **Easy concurrency:** this is one of the great appeals of the language thanks to goroutines and channels.
- **IDE integrations:** We have great IDEs, like Jetbrains' [Goland](https://www.jetbrains.com/go/promo/) and the [Go plugin](https://code.visualstudio.com/docs/languages/go) for Visual Studio Code.
- **Dependency Management:** The language took a while to adopt this, but having dependency management built into the toolset is very useful. All it takes is one `go get` or `go mod tidy` to install the project's dependencies.
- **Static Binaries:** this speeds up application deployment, as all you need to do is compile, and we have a self-contained executable ready to run.
- **Delve Debugger:** having a powerful debugger already configured in all IDEs speeds up code development and maintenance.
- **Built-in Test Framework:** The test library being built into the standard library accelerates the adoption of techniques such as TDD.
- **Cross-compilation:** I think it's fantastic that my macOS can generate a binary for Windows, Linux, and other platforms in such a simple way.
- **Built-in Formatting:** There is no time wasted discussing how to format your code as the [linter](https://go.dev/blog/gofmt) is already in the language's toolset, and all IDEs can format your code when saving it.

[![go_security](/images/posts/go_security.png)](/images/posts/go_security.png)

- **Module Mirror Checksum DB:** This feature was [launched](https://go.dev/blog/module-mirror-launch) in 2019 and helps ensure the authenticity of the modules the application uses as dependencies.
- **Memory Safe Code:** "Memory Safe is a property of some programming languages ​​that prevents programmers from introducing certain types of bugs related to memory usage." [source](https://www.memorysafety.org/docs/memory-safety/)
- **Compatibility Promise:** This makes choosing Go much safer, especially for companies, as it avoids the need for significant future refactorings, as happened from PHP 4 to PHP 5 or from Python 2 to Python 3, for example—more details on the language [blog](https://go.dev/blog/compat).
- **Vulnerability Scanning:** The [govulncheck](https://go.dev/doc/security/vuln/) tool is essential to the language's toolset and helps us find and fix vulnerabilities in our projects.
- **Built-in Fuzz Testing:** [Fuzz testing](https://go.dev/doc/security/fuzz/) is an advanced technique that increases the test coverage surface with arbitrary values, improving code quality.
- **SBOM Generation:** SBOM stands for *Software Bill of Materials* and presents a detailed inventory of all software components and dependencies within a project. This [post](https://earthly.dev/blog/generating-sbom/) outlines some ways to generate this resource for applications.
- **Source Code Analysis:** Most commercial static code analysis solutions, such as [Sonar](https://www.sonarsource.com/knowledge/languages/go/), support Go, but there are also open-source projects, such as [golangci-lint](https://golangci-lint.run/).

[![go_performance](/images/posts/go_performance.png)](/images/posts/go_performance.png)

- **Rich Standard Library:** if the motto "with batteries included" had not been used by Python, the Go community could have adopted it. The language's native library has practically everything modern software development needs, from HTTP server/client, tests, JSON parsers, data structures, etc.
- **Built-in profiling:** The language's native library features `pprof`, which allows us to analyze application performance. I wrote about this in another [post](https://eltonminetto.dev/en/post/2020-04-08-golang-pprof/).
- **Runtime Tracing:** The team behind the language development recently [improved](https://go.dev/blog/execution-traces-2024) the tracing feature, increasing the details we can collect from applications to understand their behavior.
- **Self-tuning GC:** Go has one of the most modern Garbage Collector implementations, and more details are available on the language [blog](https://tip.golang.org/doc/gc-guide).
- **Dynamic Race Detector:** Another [feature](https://go.dev/doc/articles/race_detector) built into the language that helps detect problems faster in the development and testing phase.
- **Profile-guided Optimization:** This [feature](https://go.dev/doc/pgo) has significantly reduced resource consumption. The talk that generated this post provides more details.

I liked this way of presenting the language because it briefly shows the entire ecosystem around it and all the benefits we receive when adopting it.
What do you think of this vision? Have you ever felt this way?