---
title: Introduction to TypeSpec
date: 2024-10-12T09:00:43-03:00
draft: false
tags:
  - api
---

I'll start this post with a bit of history. Back in the early 2010s, the buzz was the concepts of APIs and API-first. It seems trivial today, but we must remember that the technology used before was SOAP and its giant XML files. So, lightweight APIs using JSON and respecting the REST concepts, which had been invented a few years earlier, were a considerable evolution.

In this context, a "dispute" began between some API specification languages; as more and more teams adopted APIs, something was needed that would make it possible to define, document, and detail them. Among the competitors, I can remember [RAML](https://raml.org) and the one I was betting my chips on, [API Blueprint](https://apiblueprint.org), but the winner was Swagger, later renamed to [OpenAPI](https://www.openapis.org).

 
 Fast forward to 2024, and everyone is happily writing and maintaining YAML files of hundreds, sometimes thousands of lines. Right?

[![apidoc1](/images/posts/tracy-morgan-bruce-willis.gif)](/images/posts/tracy-morgan-bruce-willis.gif)

I'm not the only one with this impression because, in early 2024, Microsoft launched a new Open Source project called [TypeSpec](https://typespec.io). It is a format based heavily on TypeScript, also created by Microsoft and simplifies the definition of APIs.

Anyone who closely follows the history of the OpenAPI standard may find this move quite strange since Microsoft is one of the standard's [member](https://www.openapis.org/membership/members) entities. In a [talk](https://www.youtube.com/watch?v=yfCYrKaojDo), one of the leaders of the TypeSpec project commented on the reasons for creating the new standard:

- OpenAPI was perceived by Microsoft teams as being hard to write, review, and maintain.
- It was difficult to follow API guidelines in this context, especially when working with many new teams that did "know" OpenAPI.
- OpenAPI does not rule the Microsoft world in terms of API description languages and protocols, with others such as gRPC being supported.

At this point, some of the readers may be asking themselves something like:

> I don't see any problems with OpenAPI; I generate the documentation from my code!


Indeed, this is an approach some teams adopt, using tools like [swaggo](https://github.com/swaggo/swag). I see some problems with this approach, such as code being "polluted" with [comments and annotations](https://github.com/swaggo/swag?tab=readme-ov-file#how-to-use-it-with-gin) and the fact that the API is heavily influenced by the team's programming style.


However, there is another approach, which, in my opinion, is the best: design-first. In this approach, teams first define the API design, considering its consumers, the resources that will be exposed, and its evolution. With this in mind, it is necessary to have a simple way to document this design, which will later be implemented and used by clients. And I wouldn't use the word "simple" to define a document written in the OpenAPI standard…

Let's get our hands dirty and test this out.

The first step is to install the TypeSpec CLI using the command:

```bash
npm install -g @typespec/compiler
```
	
In the next step, I created a directory and initialized a project:

```bash
mkdir post-typespec
cd post-typespec/
tsp init
```

I selected `Generic REST API` in the options menu and accepted the default options.

The following structure was created:

```bash
❯ ls -lha
total 32
drwxr-xr-x   6 eminetto  staff   192B 13 Out 09:51 .
drwxr-xr-x  80 eminetto  staff   2,5K 13 Out 09:50 ..
-rw-r--r--   1 eminetto  staff   102B 13 Out 09:51 .gitignore
-rw-r--r--   1 eminetto  staff    79B 13 Out 09:51 main.tsp
-rw-r--r--   1 eminetto  staff   417B 13 Out 09:51 package.json
-rw-r--r--   1 eminetto  staff    31B 13 Out 09:51 tspconfig.yaml
```

The file `package.json` contains the project dependencies, which can be installed with the command:


```bash
tsp install
```
	
Now we have the dependencies installed, we can describe our API in the file `main.tsp`. In this case, I used the [example](https://typespec.io/openapi) from the official documentation:


```typescript
import "@typespec/http";

using TypeSpec.Http;

model Pet {
  name: string;
  age: int32;
}

model Store {
  name: string;
  address: Address;
}

model Address {
  street: string;
  city: string;
}

@route("/pets")
interface Pets {
  list(@query filter: string): Pet[];
  create(@body pet: Pet): Pet;
  read(@path id: string): Pet;
}

@route("/stores")
interface Stores {
  list(@query filter: string): Store[];
  read(@path id: string): Store;
}
```

With this definition, we can generate the specifications using the concept of Emitters. I will return to this subject shortly, but we will use it to generate the OpenAPI specification for now. To do this, we will use the file `tspconfig.yaml`, which has the following generated code:


```yaml
emit:
  - "@typespec/openapi3"
```

When using the following command, we have the document generated inside the directory `tsp-output/@typespec/openapi3/openapi.yaml`:


```bash
tsp compile .
```
	
You can also use the following command to have the compilation run automatically as files within the project change:


```bash 
tsp compile . --watch
```

Doing a file size comparison, the 31 lines of TypeSpec become 125 lines of OpenAPI in YAML format! The two files can be compared in the official [documentation](https://typespec.io/openapi).

Getting back to Emitters. In addition to the [OpenAPI](https://typespec.io/docs/emitters/openapi3/reference) we saw in action, there are options for generating specifications in [JSON Schema](https://typespec.io/docs/emitters/json-schema/reference) and [Protobuf](https://typespec.io/docs/emitters/protobuf/reference). But more importantly, it is possible to create [new emitters](https://typespec.io/docs/extending-typespec/emitters) for generating codes, SDKs, other documentation formats, etc. For example, we could have an Emitter that reads the specification in TypeSpec and generates configurations for some API Gateway like [Kong](https://konghq.com/products/kong-gateway) or [Traefik](https://doc.traefik.io/traefik/) (I gave this example because it is something I want to test).

One argument against adopting TypeSpec could be the downfalls of including a new component in the team's stack. Another could be the low maturity of the project, as it is less than a year old (as an Open Source project), despite its rapid evolution and use within Microsoft.

These valid arguments should be considered whenever a new technology is considered for a project. My goal with this post was to present this new option and suggest we take a closer look at it, as it can be very valuable in large projects. I will keep it on my list of tools to test and validate.

What is your opinion on the subject? I would love to hear opinions, especially from `#teamOpenAPI` :)