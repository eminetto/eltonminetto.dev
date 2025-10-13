---
title: Go should be more opinionated
date: 2025-06-19T10:00:00-03:00
draft: false
tags:
  - go
---

One of the perks of being a [Google Developer Expert](https://g.dev/eminetto) is the incredible opportunities it provides. A few weeks ago, I had the opportunity to meet [Robert Griesemer](https://en.wikipedia.org/wiki/Robert_Griesemer), co-creator of Go, in person, as well as [Marc Dougherty](https://www.linkedin.com/in/doughertymarc/), Developer Advocate for the Go team at Google. At a happy hour after Google I/O, Marc asked me and another Go GDE from Korea for feedback on the language. My response was that I didn't have any specific feedback about the language but that:


> Go should be more opinionated about the application layout.

It was worth writing a post to express my thoughts more clearly.

Starting from the beginning… 

In 2025, I will have completed 10 years of writing code in Go. One of the things I recall from when I started is that the language was relatively simple to learn, mainly due to two reasons: its simplicity and the fact that there is only one way to do things. Go was the first language I came across that had strong opinions about several things. There is only one way to loop, and there is only one way to format files (using the 'go fmt' command). Variables with a small scope should have short names, etc. It made it much easier to read code written by other people, which is crucial for learning. The code I wrote was very similar to the Kubernetes code! Of course, the complexity of the problem was infinitely greater, but the code's structure was readable to me. Over the years, I have observed this effect in several people I have followed who were starting in the language or migrating from other environments.

But once this initial excitement has passed, the biggest challenge comes: how to adopt Go in a project larger than those used for learning? How do you structure a project that will be developed and evolved by a team? At this point, the language step aside from strong opinions, and each team or company needs to decide how to structure their projects. Over the past decade, I have worked for four companies. In all of them, it was necessary to invest the team's time in collecting examples and reading documentation and books to determine which structure they should use in the projects. At the company where I currently work, we have created a [document](https://medium.com/inside-picpay/organizing-projects-and-defining-names-in-go-7f0eab45375d) about this.

Making an analogy with the world of games, it's as if we were having fun in the controlled and wonderful world of Super Mario World and were transported to the open world of GTA 6 (yes! I'm hyped!). It's still a fantastic universe, but the transition is quite abrupt.

Go could be more opinionated regarding these choices. We could have templates for more common projects, such as CLIs, APIs, and microservices., that teams can use to scaffold their applications. The language toolkit [already allows the use of project templates](https://go.dev/blog/gonew), so it would be a matter of having official templates to make life easier for teams. Alternatively, we could go further and include the command in the language toolkit itself with something like `go new`.

A similar event occurred in the history of the language. Today, `go mod` dependency management is a fundamental part of our daily lives as Go developers. But it wasn't always like this. For a long time, there was no official package manager for the language; consequently, the community developed several alternatives. They all worked, but fragmentation was getting out of control, making it challenging to integrate packages. Until the language team took control of the situation and `go mod` was created, pacifying the issue of "package and dependency management." I believe we can apply the same approach to the structure of projects.

Another profile that would benefit from a more opinionated project structure is that formed by teams that are migrating their applications from other languages, especially Java and PHP. In these ecosystems, frameworks dictate the structure of projects, such as Spring Boot and Laravel. "Where do I start? How do I structure my project?" are common questions I hear from teams migrating from these languages. Having something that facilitates this migration would lower the barrier to entry and increase the number of teams experimenting with Go in production.

That's my biggest feedback regarding Go at the moment. What do you think, dear reader? What's your opinion on the subject? I'd love to discuss this topic in the comments or live at a conference.