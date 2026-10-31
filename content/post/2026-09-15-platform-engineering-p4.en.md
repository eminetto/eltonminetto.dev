---
title: Platform Engineering and Observability
date: 2026-09-15T06:30:43-03:00
draft: false
tags:
  - platform-engineering
---

This post is part of my [series](https://eltonminetto.dev/en/tags/platform-engineering/) on platform engineering, but it’s also a follow-up to a subject I’m very interested in: observability.

A while ago, I [wrote](https://eltonminetto.dev/en/post/2025-01-28-o11y-great-architectures/) a post (which later became a talk) about how observability underpins great software architectures. Today, I believe its role is even bigger: observability underpins business decisions (productivity, costs, performance, customer acquisition, etc.), far beyond troubleshooting. In this post, I want to focus on how platform engineering and observability are intertwined.

In the post on [how to get started](https://eltonminetto.dev/en/post/2026-07-04-platform-engineering-p2/#how-to-get-started) with a platform, I mention that one of the key steps is “Collect metrics”, so this role is already quite clear. The platform itself heavily uses the features observability provides: usage and resource consumption metrics, audit logs, traces to pinpoint errors, and user behavior events. Without this information, the platform team is flying blind and can’t evolve its product (remember the product mindset, a prerequisite for building platforms).

But its fundamental role is to do what some authors call “shift-down” (as opposed to “shift-left”, where developers instrument their own code). Platform teams can support product teams by providing tools such as SDKs and automated collectors, so that applications’ baseline telemetry is collected. There are common patterns the platform can collect automatically:

* RED metrics: Rate, Errors, and Duration
* USE: Utilization, Saturation, and Errors
* Four Golden Signals: Latency, Traffic, Errors, and Saturation

With the platform collecting baseline telemetry, product teams can focus on instrumenting what is specific to their products, usually business metrics and events.

Another important area platform teams can influence is standardizing observability data. By adopting an industry standard such as [OpenTelemetry](https://opentelemetry.io/), the company gets important benefits, such as:

* All teams and services share the same information in the same format, which is crucial when troubleshooting an incident;
* There’s no vendor lock-in. Observability services tend to be major budget consumers, so being able to switch vendors based on how negotiations go is very useful;
* Since all services share the same telemetry format, an AI agent can analyze the data and find solutions more easily, spending fewer tokens on conversions and normalization.

In 2026, three important disciplines need to work together: platform, FinOps, and AI. AI can be both a culprit, generating an unprecedented volume of telemetry, and a powerful tool that identifies patterns and helps solve problems. By applying the points I’ve covered in this post, the platform delivers major benefits to product teams. But the costs of both AI and observability can easily spiral out of control, so continuous cost monitoring is a must. Still, when these teams are aligned, there’s great transformative potential to drive the business forward, which is the ultimate benefit.