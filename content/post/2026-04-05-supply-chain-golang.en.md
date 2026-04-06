---
title: Avoiding supply chain attacks in Go
date: 2026-04-05T19:00:43-03:00
draft: false
tags:
  - go
---

If you've been following the news in recent weeks (March/April 2026), you must have read about two major "supply chain attacks" that occurred. You were probably affected in some way by the problem with the [LiteLLM](https://docs.litellm.ai/blog/security-update-march-2026) and [Axios](https://www.elastic.co/security-labs/axios-one-rat-to-rule-them-all) projects.

Let's take a step back and read the definition of this type of attack, according to the Google Gemini summary:

> A supply chain attack is a cyberattack that targets an organization by exploiting its trusted relationships with third-party vendors, suppliers, or software providers. Rather than attacking a well-defended target directly, attackers compromise a weaker partner to infiltrate the ultimate target's network, often using malicious updates or compromised software.

A good concept to keep in mind is that each external dependency you add to your project increases the "attack surface," the possibility that components you are not directly responsible for can be used to harm your company/project.

In this post, I want to discuss how to mitigate this category of attacks using concepts and tools from the Go toolkit.


## Use the ~~force~~ stdlib, Luke.

Although the slogan “Batteries Included” is from the Python language, it also applies to Go. The language's standard library (stdlib) has everything that the vast majority of projects need to be developed. A very common question from those starting out with the language, especially coming from other environments, is which framework or library to use to create an API, for example. Although there are mature projects like Gin, Chi, and Fiber that make the developer experience more productive, the cost in terms of security and, often, performance doesn't seem worthwhile. The stlib package http has evolved significantly in recent versions, and I currently see no reason not to use it, especially in the context of tools like Claude and Copilot, which generate high-quality code. Instruct your AI Agents to always use stdlib , and your code will be less vulnerable to the attacks mentioned earlier.

Another advantage of using stdlib is compatibility with new versions of the language, a fundamental characteristic of the Go project. When using a third-party package, there is no guarantee that a new version of the project will not break your code, or that a vulnerability will not take a long time to be resolved and leave your project unprotected.

One exception to this rule concerns testing. Since the test code (the _test.go files in your project) is not included in the binary that will run in production, I'm not as picky about choosing useful packages like [testify](https://github.com/stretchr/testify) or [Testcontainers](https://golang.testcontainers.org). Even so, I recommend following the next tip: trust with caution.

## Trust, but not too much.

If you choose to use external dependencies in your project, the first tip is to search the website [https://deps.dev](https://deps.dev). I wrote a [post](https://eltonminetto.dev/en/post/2023-04-19-choosing-dependencies-using-deps-dev/) dedicated to this subject, but you can simply access the site and search for the dependency you plan to use, such as [Testcontainers](https://deps.dev/go/github.com%2Ftestcontainers%2Ftestcontainers-go), to find important information like Security Advisories, licenses, and the dependencies the package imports. This last item is important because sometimes we use a package without remembering that it imports other projects, and some part of this chain may be compromised.

Searching in deps.dev is a good first step, but tools evolve, as do their vulnerabilities. To ensure nothing new harms your project, it's important to automate this process, and for that, we should use another important tool from the Go toolkit: govulncheck. On the language's official website, you can [find](https://go.dev/doc/tutorial/govulncheck) a tutorial on how to use it. Remember to add govulncheck as a step in your CI/CD pipeline so that new vulnerabilities break the build process as soon as they are detected.

Anyone who works in security knows it's a constant "cat and mouse" game with attackers and attacks, and that it's impossible to reach the "nirvana" of 100% security. But following tips like the ones I've mentioned here can help us a lot and should be part of the daily routine of any development team.
