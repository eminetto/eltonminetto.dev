---
title: Google Cloud Next 2026 Recap
date: 2026-05-07T07:00:43-03:00
draft: false
---

From April 22nd to 24th, I attended Google Cloud Next in Las Vegas, where Google unveiled updates to GCP and related technologies. For two years, I participated in Google I/O through the Developer Expert (GDE) program. This year, the program brought us to Next, which is the sister event to I/O held in California in May.

> A curious fact: there are currently 1367 people worldwide with the title of GDE. Of these, only 17 are GDEs in Go, including 3 Brazilians: [Tiago Temporin](https://www.linkedin.com/in/tiago-temporin/), [Alexandre Cabral](https://www.linkedin.com/in/alexandre-cabral-bedeschi/) and me.

Next is a large trade show for companies related to GCP, including "competitors" like GitHub Copilot and Anthropic, as well as well-known companies like  Databricks, ClickHouse, ZScaler, Oracle, and IBM. Only AWS isn't there, for obvious reasons :). Besides the trade show, the event features two more floors of lectures running continuously. And some companies hold lectures in their own space at the show, as was the case with Atlassian, Anthropic, etc. 

## GDE Summit

On Monday and Tuesday, we had a private event for GDEs, where we attended interesting lectures and had the opportunity to talk with Google's product teams about the areas in which we are experts. I participated in a conversation with [Marc Dougherty](https://www.linkedin.com/in/doughertymarc/), from the Go team. We were able to offer suggestions, ask questions about the future of the language, etc. Among the suggestions, I reinforced what I had already mentioned in this [post](https://eltonminetto.dev/en/post/2025-06-19-go-more-opinated/). And another suggestion was that it would be interesting to have an official Go Skill for AI agents (more on that in the next paragraphs) to generate more idiomatic code. Whether these suggestions will have any effect, only time will tell ;)

Among the insights from that first event, I saved a few:

[![gnext1](/images/posts/GoogleCloudNext/1_skills.jpg)](/images/posts/GoogleCloudNext/1_skills.jpg)

The important thing in this image is the link [https://agentskills.io/home](https://agentskills.io/home)

[![gnext2](/images/posts/GoogleCloudNext/2_skills.jpg)](/images/posts/GoogleCloudNext/2_skills.jpg)

They announced the release of official skills for GCP products: [https://github.com/google/skills](https://github.com/google/skills) , which can be very useful for connecting agents to their tools. Another related topic was the announcement that all GCP products now have native support for MCP.

[![gnext3](/images/posts/GoogleCloudNext/3_skills.jpg)](/images/posts/GoogleCloudNext/3_skills.jpg)

I liked this reflection on the questions the Agents bring to our daily lives, and on what changes and what doesn't.

[![gnext4](/images/posts/GoogleCloudNext/4_sobre_agents.jpg)](/images/posts/GoogleCloudNext/4_sobre_agents.jpg)

[![gnext5](/images/posts/GoogleCloudNext/5_oque_nao_muda.jpg)](/images/posts/GoogleCloudNext/5_oque_nao_muda.jpg)

[![gnext6](/images/posts/GoogleCloudNext/6_oq_muda.jpg)](/images/posts/GoogleCloudNext/6_oq_muda.jpg)

## Cloud Next

Next started on Wednesday with this [keynote](https://www.youtube.com/watch?v=11PBno-cJ1g). I highly recommend it as it discusses the business side of GCP and the latest news and partnerships (such as Apple, which was highlighted in the presentation). I saved a few things from this talk:

[![gnext7](/images/posts/GoogleCloudNext/7_geap.jpg)](/images/posts/GoogleCloudNext/7_geap.jpg)

This was the big launch of the event. They renamed a product called VertexAI to *Gemini Enterprise Agent Platform* (Google proving they still need to improve on "product naming"). They want to become the best platform for hosting and managing the lifecycle of AI Agents, dividing all products into 4 pillars:

- Build
- Scale
- Govern 
- Optimize

The next two images show the platform's size.

[![gnext8](/images/posts/GoogleCloudNext/8_geap.jpg)](/images/posts/GoogleCloudNext/8_geap.jpg)

[![gnext9](/images/posts/GoogleCloudNext/9_geap.jpg)](/images/posts/GoogleCloudNext/9_geap.jpg)


In the Build pillar, the standout player was the [ADK](https://adk.dev/)

[![gnext10](/images/posts/GoogleCloudNext/9_adk.jpg)](/images/posts/GoogleCloudNext/9_adk.jpg)

Several talks during the event showed how to create agents with the ADK. I attended two talks about ADK + Go, but there is also support for other languages ​​.

On Wednesday, they presented the [Developer Keynote](https://www.youtube.com/watch?v=A01DQ8_xy7Q), which featured demos of each pillar, and I highly recommend it to understand how everything integrates. They considered the entire lifecycle of an Agent, from build to things like security and observability.

Another interesting aspect of the event is that everything presented in the Keynotes can be replicated via Google Codelabs, including GCP credits, so you can test it on their infrastructure.

## My insights

My main conclusion is that agents are truly the market's big bet, because a giant like Google wouldn't invest so much in something that didn't offer a real return. Another point that was repeated quite a bit is that the experimentation phase is over; now is the time to look at AI (LLMs, Agents) seriously and responsibly, considering costs (several talks on FinOps and companies showcasing tools), performance, scalability, etc.

Another topic that greatly interested me was the launch of [Gemma 4](https://deepmind.google/models/gemma/gemma-4/). This is Google's open-source model, which allows us to create our own "Gemini." I believe this concept will grow significantly in the coming months as companies run models across their clusters and gain greater control over costs, performance, and optimization. It's a subject I want to delve into in the coming months.

One point to emphasize is that even if Google isn't your primary cloud provider, much of the knowledge can be applied, especially concepts like Agent Registry, Agent Gateway, and Observability (a presentation covered the concepts of Investigation Agent and Optimize Agent, which investigate incidents and optimize performance and costs). I recommend watching the Developer Keynote to fully grasp these concepts.

[![gnext11](/images/posts/GoogleCloudNext/10_optimize.jpg)](/images/posts/GoogleCloudNext/10_optimize.jpg)

If you have any questions about a topic I've left out, feel free to contact me in the comments on this post or on LinkedIn, and I can elaborate.