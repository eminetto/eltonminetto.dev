---
title: "Load Test Types"
date: 2024-01-05T19:00:43-03:00
draft: false
---

When we talk about "load testing," perhaps the first thing that comes to mind is "sending traffic to the application until it cries." 🙂 But this approach is just one way to test an application's performance (and described in this form is perhaps the most sadistic). 

In this post, I will present the main types of load testing, and in the [subsequent text](https://eltonminetto.dev/en/post/2024-01-11-load-test-k6/), I will show how to implement them using the [k6](https://k6.io) tool.

The main types of load tests differ in two aspects: their objectives and how we carry out the tests. Let's go to them.

## Smoke testing

Also known as "Build Verification Testing" or "Build Acceptance Testing".

### Goals

- Test the basic functionality of the application. Before testing whether the application supports hundreds or thousands of users, we need to ensure that it is functional and will act correctly with one or a few users.
- In addition to providing basic functionality, it serves as a baseline for future tests, as we can use its results as a parameter for future tests. For example, if the application runs in X milliseconds for one user, we can use this value to compare with 100 or 1000 simultaneous users.

### How to test

The smoke test is the most straightforward; we access the API or system using just one user, or a tiny number, for a few seconds and analyze the result.

## Load testing

### Goals

- Test the expected system load. For example, if we anticipate that 1000 users will access the API, this is the value we will use in tests.
- Ensure that the minimum performance is always as expected. For comparison purposes, we can use the data generated in the Smoke Test, some market standard, or some regulation to which the system is subject.

### How to test

It is important to remember that we simulate normal user behavior in all load tests. That's why it's essential to think that, except in the Spike test we'll see later, the load increases gradually and not all at once. And it doesn't magically disappear, either. That's why the tests have a "ramp-up" phase, where we gradually increase the load, and a "ramp-down" phase, where the load decreases until it stops. With this, we can also evaluate how our system behaves concerning elasticity (including and removing resources as necessary).

[![LoadTest](/images/posts/LoadTest.png)](/images/posts/LoadTest.png)

## Stress testing

### Goals

- Add more load than usual.
- Tests how the system behaves under pressure, answering questions such as "How does the system behave with 10% more load? And with 50% more?"

### How to test

[![StressTest](/images/posts/StressTest.png)](/images/posts/StressTest.png)

## Spike testing

### Goals

- Add a load peak to observe how the system behaves in these scenarios.
- Answer questions like "What happens if thousands of users suddenly access our API or system?"

### How to test

In this case, the test will simulate an instantaneous increase in accesses, which will decrease at the same speed.

[![SpikeTest](/images/posts/SpikeTest.png)](/images/posts/SpikeTest.png)

## Breakpoint testing

### Goals

- Force a load on the system until it breaks. This test is the one I mentioned at the beginning, "send traffic to the application until it cries" 🙂
- Identify the breaking point of the environment.

### How to test

[![BreakTest](/images/posts/BreakTest.png)](/images/posts/BreakTest.png)

## Soak testing

Also known as "endurance testing," "capacity testing," or "longevity testing."

### Goals

- Test how the system behaves under constant load over a long time
- Help identify memory leaks or how the system behaves when resources such as memory, disk, and database are exhausted

### How to test

[![Soaktest](/images/posts/Soaktest.png)](/images/posts/Soaktest.png)

These are the main tests to validate the different aspects of our system or API. Now that we have a knowledge base, I will show examples of how to develop these tests using the k6 tool in the next post.

# Sources

[https://k6.io/docs/test-types/load-test-types/](https://k6.io/docs/test-types/load-test-types/)

[https://www.udemy.com/course/k6-load-testing-performance-testing/](https://www.udemy.com/course/k6-load-testing-performance-testing/)