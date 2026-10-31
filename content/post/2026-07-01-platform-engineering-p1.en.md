---
title: About Platform Engineering
date: 2026-07-01T20:00:43-03:00
draft: false
tags:
  - platform-engineering
---

For the past four years, platform engineering has been part of my work and studies, so I’m starting a series of posts about what I’ve learned along the way.

First, this content doesn’t necessarily reflect the opinions and decisions of the company I work for. Every company has its own needs and characteristics, which I’ll mention often in these posts.

But first…

## What is platform engineering?

Let’s take a step back.

According to the excellent book [Platform Engineering: A Guide for Technical, Product, and People Leaders](https://a.co/d/09WvMdz3), by Camille Fournier and Ian Nowland (I’ll quote this book many times, as it’s one of the main references on the subject):

> A platform is a foundation of self-service APIs, tools, services, knowledge, and support that are arranged as a compelling internal product. Autonomous application teams can use the platform to deliver product features faster, with less coordination.

Therefore,

> Platform engineering is the discipline of developing and operating platforms. The goal of this discipline is to **manage overall system complexity** to deliver leverage to the business. It does this by taking a **curated product approach** to developing platforms as software-based abstractions that serve a broad base of application developers, operating them as the business's foundation.

The emphasis is mine, because these are two points I consider very important.

I really like this quote from the French sociologist and philosopher Edgar Morin:

> Complexity is not eliminated; it moves, and its form changes.

Software development, and the problems we solve with code, is complex by nature. One of the roles of platform engineering is to move part of that complexity away from the “product teams”.

A simplistic distinction is useful here, and I’ll go into more detail in a later post in this series:

* the “platform team” is the team responsible for building and maintaining the platform;
* the “product team” is the team that uses the platform to build other products more easily.

Better definitions exist, but for the purposes of this post, I’ll stick with these.

The other point I highlighted in the definition of platform engineering is the “product approach”. Historically, teams have built scripts and small applications to solve day-to-day problems, whether to simplify builds and deployments, generate data for tests, automate machine setup, and so on. But these small automations have always focused on the person or team who built them. When we scale this up to a platform, we need to look at the company as a whole, at the other teams, at different levels of knowledge, etc. A common trap platform teams fall into is assuming that, because they are developers/SREs themselves, they are the customer, and failing to create active listening processes to understand the real pain points of the platform’s other users. That’s why it’s important to have a product mindset from the very beginning.

## Where to start?

Now that we know the main concepts, the next questions are probably “where do we start?” and “how do we start?”. In this post, I’ll cover the first question and leave the second for upcoming posts.

The simplest answer would be: “it depends” :) The most accurate one would be: “where it hurts the most”.

Remember the “product approach” I mentioned in the previous section? This is one of the moments when it becomes necessary. The first step is to talk to the platform’s customers, the “product teams”. Ask and observe where teams experience the most friction in the [software development life cycle (SDLC)](https://www.ibm.com/think/topics/sdlc), and start solving those problems.

If a team takes days to set up a new project, a first deliverable could be an automation that, with a few clicks, creates a new repository and sets it up on the developer’s machine or in QA in minutes. This could be done with a CLI, a script, a custom UI, or a tool like [Backstage](https://backstage.io/).

If a team takes hours to build and deploy a new feature, optimizing the CI/CD pipeline will pay off significantly.

If a team struggles to identify the root cause of a production incident, improvements to the observability stack will work wonders for system downtime.

These are just a few examples. The idea is to borrow yet another product concept: the MVP (Minimum Viable Product). In our case, we can take some poetic license and call it a Minimum Viable Platform.

With these first deliverables, the platform team starts earning recognition from the other teams and takes the first step toward building something increasingly complete.

## Next steps

In this post, I’ve tried to give just a brief introduction to the terms and concepts. In the next posts, I plan to dig deeper into other related topics, but I’d love your input, dear reader. Use the comments section or message me on [LinkedIn](https://www.linkedin.com/in/eminetto/) with suggestions for topics or questions you’d like to see covered in this series.