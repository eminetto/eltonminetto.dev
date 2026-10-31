---
title: Types of Platforms and How to Get Started
date: 2026-07-04T08:00:43-03:00
draft: false
tags:
  - platform-engineering

---
In my [previous post](https://eltonminetto.dev/en/post/2026-07-01-platform-engineering-p1/), I promised to answer the question “how do we start?”, but I want to step back and introduce another definition.

## Types of platforms

From my point of view, platforms fall into two categories, based on the abstractions they provide to product teams:

* **Feature abstraction**: imagine an application made up of several microservices, each of which needs authentication and authorization. If each service built its own solution, we would end up with duplicated code and effort, plus different and incompatible standards. So it makes sense to create an “identity platform” (or something similar) that provides this capability to the whole company. The identity platform team becomes responsible for maintaining and evolving it, delivering reliable, solid APIs so the other teams can stay focused on their business rules.
* **Infrastructure abstraction**: in this case, the platform team creates abstractions that make it easier to use infrastructure resources such as databases, networking, caching, etc. A simple example: when you commit a specific .yaml file, the pipeline automatically creates an AWS S3 bucket with all the security and backup settings in place. This way, product teams don't have to deal with the complexity of the cloud provider (or providers).

These definitions should help in understanding this post and the ones that follow.

Now I’ll try to keep my promise by suggesting a small “framework” for starting to build your platform.

## How to get started

As I mentioned before, a key part of platform engineering is the product approach. So it should come as no surprise that this section talks a lot about it.

To illustrate the idea:

[![framework-plataform](/images/posts/framework-plataform.png)](/images/posts/framework-plataform.png)


As an example:

* **Identify the pain point**: teams report that deploying new applications to production takes too long, which slows down delivery and incident resolution.
* **Collect metrics**: by collecting CI/CD pipeline metrics, you find that building a new version takes 30 minutes and deploying it takes another 15, for a total of 45 minutes between merging the Pull Request and the new version being available to users.
* **Define the goal**: after talking with the product and platform teams, the goal is set to reduce the process time by at least 50%.
* **Implement the improvement**: the platform team investigates the process, finds that most of the time is spent downloading dependencies, and implements a cache. It also scales up the machines/Kubernetes pods that run the CI/CD pipeline, adding more memory and CPU.
* **Compare against the goal**: once the improvement is in place, the team collects new metrics and the whole process now takes 20 minutes, beating the 22.5-minute goal.
* **Decide**: by comparing the new time with the goal, the teams can decide to consider the iteration a success and move on to the next pain point, or keep optimizing the process.

Again, this flow is very similar to a product team’s routine; the only difference is that the customers are other developers/SREs.

By running this cycle continuously, you can deliver new improvements to teams without resorting to overengineering. Of course, other product practices, such as defining a roadmap, still matter, but short delivery cycles bring rewards and learning to every team involved.

## Next steps

In the next posts in this series, I want to talk about the profiles and roles of platform and tooling teams, as well as the impact AI has been having on this field. If you’d like to suggest other topics or contribute to the discussion, please use the comments.