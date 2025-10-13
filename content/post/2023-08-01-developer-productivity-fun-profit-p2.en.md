---
title: "Developer productivity for fun and profit - Part 2"
date: 2023-08-01T08:30:43-03:00
draft: false
---

This text is the second part of a series of posts about productivity. In the [first part](https://eltonminetto.dev/en/post/2023-01-25-developer-productivity-fun-profit-p1/), I discussed how developers can improve their productivity. In this text, I will mention some ways in which the company or team can improve the daily lives of developers.

## Do Onboarding

Starting a new company, team, or project is stressful in itself. The idea is for the person to start being productive as soon as possible, generating much pressure. One way to improve this scenario is to have a well-structured onboarding process.

One practice I tested several times was:

- In the first days, the person works in pair programming with the team, each day with someone different. This way, she learns the project details and gets along with the team.
- In the second week (or before, depending on the team size), the person receives a carefully chosen task to perform the entire development cycle (coding, testing, deployment in a homologation and production environment) autonomously.

There are several ways to carry out this onboarding process, but the most important thing is for the team to carefully define it so that new people become productive as soon as possible.

## Create a culture of documentation

It's very frustrating when we're developing a feature, and we're stuck waiting for an answer to a question or clarification that's just in someone's head. To resolve this problem, creating a culture of documentation in the team is essential. Design docs, RFCs, ADRs, and videos; there are many ways to accomplish this. Another critical point is that all these documents are structured and easy to search and consult. Tools like Confluence, Github/Gitlab Wikis, and Notion are good choices for this purpose.

## Set standards

Another vital point to accelerate development is to have well-defined standards. A good set of definitions will help with code writing, code review, and future maintenance, saving time from arguing about "tabs or spaces?" and similar topics.

Most languages have coding style standards that teams can adopt. It is possible to document and adopt a standard among the group if it does not exist. And it continues beyond there because we can define standards concerning the creation of APIs (Rest x RPC? URls in the singular or plural?), documentation (as mentioned above), and [microservices](https://microservices.io/patterns/index.html).

## Decrease the cognitive load

Software development itself is complex. In addition, the person must understand the business details for which he is writing solutions. Any complexity beyond these can decrease productivity and are good opportunities for improvement. For example:

- Make infrastructure and build/deploy processes transparent to devs.
- Adoption of libraries that implement features such as logging, authentication, authorization, caching, and observability, which are common to a large number of scenarios
- Automated quality control with tools like Sonar or Codeclimate
- Creating new projects using templates
- Collection of productivity metrics
- Optimization of application build and deployment time
- Ease in creating environments such as local and QA.


## Create/Use an Internal Development Portal

The idea is to have a central point where people can find standards, documentation, and designs. The team can do this with a specialized tool like [Backstage](https://backstage.io/), Confluence, Github, Google Docs, or some internal implementation. The software is not the most important thing here, but having an easy way to find what is needed for the person to be more productive.

## Create useful templates

Nothing is more frustrating than handing a blank page and asking the person to create a complex document or a new feature. It's easy to look at the page and think, "Where do I start?". A way to solve this is to create templates for:

- Documents. Such as design docs, ADRs, RFCs
- Projects. It is possible to do this with [templates](https://docs.github.com/en/repositories/creating-and-managing-repositories/creating-a-template-repository) from Github repositories, Backstage, or some internal solution.
- Stories and tasks in tools like Jira or [Github](https://github.blog/2016-02-17-issue-and-pull-request-templates/)
- [Pull requests](https://github.blog/2016-02-17-issue-and-pull-request-templates/)
- Commits. For this, I like the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) pattern and [commit templates](https://gist.github.com/lisawolderiksen/a7b99d94c92c6671181611be1641c733).

## Create processes for incidents.

One thing is sure: there will be some incidents in production. Some not mapped scenarios will occur, a database will be overloaded, and the cloud provider will have a problem. At these times, it is important to have well-defined processes to guide the actions to mitigate the problem, correct and document what happened so that it does not happen again, the famous post mortem.

Despite the idea that incidents are rare events, it is essential to consider them as something that can affect teams' productivity. If the team wastes much time solving a problem in production and does not learn from the occurrence, they tend to be repeated and consume even more time.

## Create a culture of quality

This tip is related to the previous one. To avoid incidents and prevent the code from becoming complex and hard to maintain, teams must have a culture of writing quality code. Changing low-quality and complex code is very frustrating and time-consuming, increasing the likelihood of errors and incidents.

A paper published by Google points out how quality directly influences the productivity of teams: [What Improves Developer Productivity at Google? Code Quality](https://research.google/pubs/pub51783/).

# Conclusions

These are just a few tips I've tried to list here, but the list could be more comprehensive, and I'd love to read your suggestions in the text comments.
