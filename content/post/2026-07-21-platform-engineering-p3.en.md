---
title: Platform Engineering Teams
date: 2026-07-21T20:00:43-03:00
draft: false
tags:
  - platform-engineering
---

Continuing [my series](https://eltonminetto.dev/en/tags/platform-engineering/) on platform engineering, I want to share my view on platform teams.

The first question we usually ask is:

> When does it make sense to set up a platform team?

In the past, I’ve read a few posts that tried to come up with a mathematical rule to answer this question:

* This [post](https://platformengineeringcost.com/team-structure) suggests 1 platform engineer for every 8 to 12 product engineers, with a minimum viable team of 2-3 people, sub-teams forming at around 80 engineers, and a dedicated manager needed from 120 engineers onward.
* This Gartner [thread](https://www.gartner.com/peer-community/post/how-many-engineers-focus-platform-right-size-platform-engineering-team-at-enterprise-level) suggests dedicating about 10% of the engineering organization to the platform, which can drop to 5% if the platform is less complex.

Personally, I prefer to look at it in a different, simpler way:

> It makes sense to set up a platform team when the cost of not having one exceeds the cost of having it.

For example: if product teams spend X hours a week deploying applications, you can calculate the value lost to this bottleneck. If that value is greater than the cost of maintaining a team to optimize this flow, it starts to make sense to create one.

As for possible team shapes, we can use concepts from the book [Team Topologies, 2nd Edition: Organizing Business and Technology for Fast Flow of Value](https://a.co/d/0fAB9RJf). In this book, the authors define a few ways of structuring teams:

* **Stream-aligned team**. Aligned to a continuous flow of work — a business segment, product, user journey, or set of features. **This is what I called, in simplified terms, a “product team” in the previous posts**.
* **Enabling team**. Made up of specialists in a specific area (e.g., security, automated testing, observability). It doesn’t deliver features directly — it helps stream-aligned teams acquire missing capabilities, working in a temporary, consultative way, and then stepping back once the team has absorbed the knowledge. **This can be the initial shape of a platform team**.
* **Complicated-subsystem team**. Owns a part of the system that requires deep specialized knowledge (e.g., a matching engine, a pricing algorithm, a video system). It exists so not every stream-aligned team needs this rare knowledge in-house. **This can be the team responsible for the kind of platform I called “Feature abstraction” in the [previous post](https://eltonminetto.dev/en/post/2026-07-04-platform-engineering-p2/#types-of-platforms).**
* **Platform team**. Finally, this team provides self-service internal services (infrastructure, tooling, internal APIs) that stream-aligned teams consume to deliver faster, without having to understand the complexity behind them.

These structures aren’t set in stone: companies can mix them or come up with their own models, but they’re generally good starting points.

And what about the shape of the platform team itself? What does it look like?

Some articles on the [Platform Engineering](https://platformengineering.org/) portal, one of the best sources of knowledge on the subject, suggest this diagram:

[![en_platform_teams](/images/posts/en_platform_teams.png)](/images/posts/en_platform_teams.png)

Again, this structure is just a suggestion. In their [book](https://a.co/d/09WvMdz3), Camille Fournier and Ian Nowland present other roles and dedicate a chapter to profiles and to suggestions on how to hire them. I recommend reading it.

In this post, I gave an overview of some concepts I consider important for platform teams, but as I’ve stressed several times, this is a very flexible subject. It can change depending on the company’s stage and the complexity involved. That’s why I’d love to hear your thoughts in the comments or on [LinkedIn](https://www.linkedin.com/in/eminetto).

