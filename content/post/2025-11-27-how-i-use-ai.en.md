---
title: How I Use AI
date: 2025-11-27T08:00:00-03:00
draft: false
---
In these almost 30 years of my career, I've seen many technologies and tools emerge (and die), and looking back, I've always had two premises about how to adopt them:

- The tool/technology should make me better
- I should not depend on it to do my work

That said, I consider the entire "revolution" (actually, I think "evolution" would be a fairer word in this context) of LLMs (and all related technologies) as just another one of these tools. In this post, I've been trying to apply these same premises nowadays.


## The tool/technology should make me better.

This is where I feel the most incredible excitement about what we have available today. Using something like Copilot (to exemplify a solution) to explain the code of a legacy project, one I don't know, or even a language I don't have experience with, greatly accelerates my learning.

But the tool I like the most, and think should have more prominence in people's daily lives, is [Google NotebookLM](https://notebooklm.google.com/). I've been using it to learn concepts more quickly. To cite a few examples where I recently used it:

[![notebooklm.png](/images/posts/notebooklm.png)](/images/posts/notebooklm.png)


I collected several sources, such as links, documents, videos, and submitted them. After the information was processed, I asked NotebookLM to create mind maps and audio and video explanations of the basic concepts. Then, I asked more advanced questions to understand the details.

The result is that I learned new subjects much faster than I would have before, which makes me a better professional and person.

My colleague,[Diego Sana](https://www.linkedin.com/in/diegosana/), recommended an [exciting approach](https://simonwillison.net/2025/Nov/6/async-code-research/) for using Agents to create small research projects for rapid learning. I really liked the approach and want to dedicate some time to test something like that.


## I should not depend on it to do my work.

This is where this text may generate some controversy. Whenever I need to develop a new project, I start by making all the architectural and code design decisions, and only then do I turn to Copilot (or another competitor). My primary language in recent years has been Go, so I start by defining the package structure, structs, and interfaces according to the language/community best practices. With this organized, I start using the LLM/Agent to help me, using prompts to generate small functions, and I review all the code before committing. I also ask it to create unit and integration tests, write documentation, and perform other repetitive tasks that add little to my knowledge. This makes the LLM "hallucinate" less because the context is smaller. It also helps me maintain control over the project's quality and expertise, which will be very useful as I support and evolve the code.

There are times when I give up this control and use an Agent to generate all the code for me. Still, usually this is for a script I'll run a few times, or even a proof of concept that will be discarded in favor of a more structured project.

And using Copilot to generate pull request documentation is one of the solutions I find most useful in daily life.

This approach makes me more productive without losing my independence from the tool.

To finish this text, I want to add some points, anticipating some possible comments 🙂

- This is how I use it in November 2025; I'll likely change something in the future.
- This is the way I use it, it doesn't mean I'm 100% correct, or that someone else is wrong. Each person has their own approach to learning and using new technologies.
- I'm not an old man complaining about new technologies, I'm also excited about all the possibilities and novelties emerging every day 🙂


And you? How have you been using these new solutions?
