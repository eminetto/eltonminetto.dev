---
title: About Platform Engineering and AI
date: 2026-10-04T09:00:43-03:00
draft: false
tags:
  - platform-engineering
---

This is another chapter in the series I’ve been writing about one of the subjects that has interested me most in recent years: platform engineering. In case you missed the previous posts, here’s a recap:

* [About Platform Engineering](https://eltonminetto.dev/en/post/2026-07-01-platform-engineering-p1/)
* [Types of Platforms and How to Get Started](https://eltonminetto.dev/en/post/2026-07-04-platform-engineering-p2/)
* [Platform Engineering Teams](https://eltonminetto.dev/en/post/2026-07-21-platform-engineering-p3/)
* [Platform Engineering and Observability](https://eltonminetto.dev/en/post/2026-09-15-platform-engineering-p4/)

As the title has probably given away, in this post I’ll talk about how AI is affecting this discipline.

## The impact of AI on platform engineering

To get started, I’ll quote a recent book I highly recommend, [Thinking in Platforms: Platform engineering as the operating model for work in the AI era](https://a.co/d/09HxGX3j):

> AI impacts platform engineering in three layers: as a user of the platform, as an interface to the platform, and as a capability within it.

> […] we believe that soon every business will run on an internal AI platform, and that it will be the job of platform engineers to build and maintain it.

These excerpts sum up well how platform teams will gain prominence in this new landscape.

A third quote I find relevant, from the same book, is:

> AI amplifies what you have; it doesn’t turn a broken system into a winning one.

Although this applies beyond platform engineering, bolting AI onto something that already has problems will only amplify its flaws. Platform teams should use AI to speed up development and user onboarding, and to help other teams adopt the technology. But without proper planning, this indiscriminate use can cause all kinds of problems, especially around security, governance, and costs.

## Platform as a maturity step

Another recent reference is a survey called [State of AI in Platform Engineering, Volume 2](https://weaveintelligence.io/research/state-of-ai-in-platform-engineering-2026), the second edition of a survey on platforms and AI, answered by 350 professionals worldwide with diverse profiles, such as platform engineers, DevOps engineers, SREs, architects, consultants, and technical leaders.

In this survey, the authors describe a maturity framework, a scale based on how much work is entrusted to agents and where humans sit relative to the loop:

* **Level 0**: Human is the loop. No agents
* **Level 1**: Human in the loop. AI suggests, humans execute
* **Level 2**: Human on the loop. Agents open PRs in parallel; humans validate behavior.
* **Level 3**: Humans as orchestrators. Agents work from signals; review by exception.
* **Level 4**: Fully autonomous. Agent systems start and complete work within guardrails.

The survey data suggests that the levels differ by the use of a platform. The report explains that, from Level 3 onward, you need governed, machine-executable paths; a validation loop that feeds failures back to the agent; a distinct identity for each agent; and isolated environments. These are classic characteristics of an internal developer platform (IDP). It’s the platform, treated as an evolution of the IDP, that turns these requirements into reusable capabilities, shifts human review to exceptions, and makes it possible to govern identity and cost. That’s why only those who rebuild their platform underneath the agents move forward.

## The evolution of the IDP

In the previous section, I mentioned an evolution of the IDP, but what does that mean? Enter a new concept: the Agentic Engineering Platform (AEP).

Using the definition from the survey:

> The AEP is presented as the evolution of the IDP, not its replacement. It’s what the IDP becomes when agents become users of the platform, rather than tools bolted onto it.

The original report goes further, detailing the possible layers of an AEP:

* **Tooling**: the five planes platform teams already operate (developer control, CI/CD, resources, security, and observability), now under agent load. Reliable APIs, predictable rate limits, and explicit failure modes stop being best practices and become prerequisites.
* **Path specifications**: deterministic, probabilistic, or hybrid paths.
* **Agent infrastructure**: harness (execution, context, capacity, evaluation), governance (identity, security, observability), and models.

As for the types of paths, the differences between them are:

* **Deterministic**: cheap governance; the default for anything auditable, irreversible, or regulated.
* **Probabilistic**: governance shifts to evaluation, and “evals become the test suite”.
* **Hybrid**: probabilistic generation wrapped in deterministic gates, looping until it passes. It costs more tokens, but it’s auditable.

Understanding these types of paths matters for the point I made at the beginning of this post. Using AI indiscriminately, without validating what actually makes sense, creates unnecessary costs and other risks. Recognizing that some platform needs can stay deterministic is key to avoiding adding AI to every feature when it isn’t needed.

## Conclusions

About this post, what I can say for sure is: we’re all learning. This is a crucial moment for every area of technology, and platform engineering is no exception. New paradigms are being created and refuted within weeks or months. The moment calls for us to keep an open mind and watch how things move so we can position ourselves better, as platform engineers and as professionals in general.

As for the topic of this series, platform engineering is a broad and constantly evolving subject, so I never intended to cover everything in this collection of posts. New books and events come out every year, and AI is speeding everything up even more. So this wraps up what I had planned for the series, but I’d love to hear your suggestions for other topics you find relevant. Leave them in the comments or reach out to me on LinkedIn; I’ll be happy to talk about it.