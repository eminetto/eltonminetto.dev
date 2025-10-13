---
title: "Introduction to Cuelang"
date: 2022-11-08T13:00:19-03:00
draft: false
tags:
  - go
---

I bet that at that moment, you are thinking:

> "Another programming language"?

Calm down, calm down, come with me, and it will make sense :)

Unlike other languages like Go or Rust, which are "general-purpose languages," [CUE](https://cuelang.org) has some particular objectives. Its name is actually an acronym that stands for "Configure Unify Execute," and according to the official documentation:

> Although the language is not a general-purpose programming language, it has many applications, such as data validation, data templating, configuration, querying, code generation, and even scripting.

It is described as a "superset of JSON" and is heavily inspired by Go. Or, as I like to think:

> "Imagine that Go and JSON had a romance, and the fruit of that union was CUE" :D

In this post, I will present two scenarios where the language can be used, but the official [documentation](https://cuelang.org/docs/) has more examples and a good amount of information to consult.

## Validating data

The first scenario where CUE excels is in data validation. It has native [support](https://cuelang.org/docs/integrations/) for validating YAML, JSON, and Protobuf, among others.

I'll use some examples of [configuration files](https://doc.traefik.io/traefik/user-guides/crd-acme/) from the Traefik project, an API Gateway.

The following YAML defines a valid route to Traefik:

```yaml
apiVersion: traefik.containo.us/v1alpha1
kind: IngressRoute
metadata:
  name: simpleingressroute
  namespace: default
spec:
  entryPoints:
    - web
  routes:
    - match: Host(`your.example.com`) && PathPrefix(`/notls`)
      kind: Rule
      services:
        - name: whoami
          port: 80
```

With this information, it is possible to define a new route in API Gateway, but if something is wrong, we can cause some problems. That's why it's essential to have an easy way to detect issues in configuration files like this. And that's where CUE shows its strength.

The first step is to have the language installed on the machine. As I'm using macOS, I just ran the command:

```bash
brew install cue-lang/tap/cue
```

In the official [documentation](https://cuelang.org/docs/install/), you can see how to install it on other operating systems.

Now we can use the `cue` command to turn this YAML into a `schema` of the CUE language:

```bash
cue import traefik-simple.yaml
```

A file called `traefik-simple.cue` is created with the contents:

```go
apiVersion: "traefik.containo.us/v1alpha1"
kind:       "IngressRoute"
metadata: {
	name:      "simpleingressroute"
	namespace: "default"
}
spec: {
	entryPoints: [
		"web",
	]
	routes: [{
		match: "Host(`your.example.com`) && PathPrefix(`/notls`)"
		kind:  "Rule"
		services: [{
			name: "whoami"
			port: 80
		}]
	}]
}
```

It's a literal translation from YAML to CUE, but let's edit it to create some validation rules. The final content of `traefik-simple.cue` looks like this:

```go
apiVersion: "traefik.containo.us/v1alpha1"
kind:       "IngressRoute"
metadata: {
	name:      string
	namespace: string
}
spec: {
	entryPoints: [
		"web",
	]
	routes: [{
		match: string
		kind:  "Rule"
		services: [{
			name: string
			port: >0 & <= 65535
		}]
	}]
}
```

Some of the items were exactly the same, like `apiVersion: "traefik.containo.us/v1alpha1"` and `kind: "IngressRoute."` This means that these are the exact values expected in all files that will be validated by this `schema.` Any value different from these will be considered an error. Other information has changed, such as:

```go
metadata: {
	name:      string
	namespace: string
}
```

In this snippet, we define that the content of `name,` for example, can be any valid `string.` In the excerpt `port: >0 & <= 65535`, we define that this field can only accept a number between 0 and 65535.

It is now possible to validate that the YAML content conforms to the `schema` using the command:

```bash
cue vet traefik-simple.cue traefik-simple.yaml
```

If everything is correct, nothing is displayed on the command line. To demonstrate how it works, I altered `traefik-simple. yaml`, changing the value of `port` to `0`. Then, when rerunning the command, you can see the error:

```bash
cue vet traefik-simple.cue traefik-simple.yaml
spec.routes.0.services.0.port: invalid value 0 (out of bound >0):
    ./traefik-simple.cue:16:10
    ./traefik-simple.yaml:14:18
```

If we change any of the expected values, such as `kind: IngressRoute` to something different, such as `kind: Ingressroute,` the result is a validation error:

```go
cue vet traefik-simple.cue traefik-simple.yaml
kind: conflicting values "IngressRoute" and "Ingressroute":
    ./traefik-simple.cue:2:13
    ./traefik-simple.yaml:2:8
```

This way, finding an error in a Traefik route configuration is very easy. The same can be applied to other formats like JSON, Protobuf, Kubernetes files, etc.

I see an obvious scenario of using this data validation power: adding a step in CI/CDs to use CUE and validate configurations at `build` time, avoiding problems in the `deploy` stage and application execution. Another scenario is to add the commands in a `hook` of Git to validate the configurations in the development environment.

Another exciting feature of CUE is the possibility of creating `packages,` which contain a series of `schemas` that can be shared between projects in the same way as a `package` in Go. In the official [documentation](https://cuelang.org/docs/concepts/packages/#packages), you can see how to use this feature and some [native](https://cuelang.org/docs/concepts/packages/#builtin-packages) `packages` of the language, such as `strigs,` `lists,` `regex,` etc. We'll use a `package` in the following example.

## Configuring applications

Another usage scenario for CUE is as an application configuration language. Anyone who knows me knows I have no appreciation for YAML (to say the least), so any other option catches my eye. But CUE has some exciting advantages:

- Because it is JSON-based, reading and writing are much simpler (in my opinion)
- Solves some JSON issues like missing comments, which was a winning feature for YAML
- Because it is a complete language, it is possible to use `if,` `loop,` built-in packages, type inheritance, etc.

The first step for this example was creating a package to store our configuration. For that, I made a directory called `config,` and inside it, a file called `config.cue` with the content:

```go
package config

db: {
	user:     "db_user"
	password: "password"
	host:     "127.0.0.1"
	port:     3306
}

metric: {
	host: "http://localhost"
	port: 9091
}

langs: [
	"pt_br",
	"en",
	"es",
]

```

The next step was to create the application that reads the configuration:

```go
package main

import (
	"fmt"

	"cuelang.org/go/cue"
	"cuelang.org/go/cue/load"
)

type Config struct {
	DB struct {
		User string
		Password string
		Host string
		Port int
	}
	Metric struct {
		Host string
		Port int
	}
	Langs []string
}

// LoadConfig loads the Cue config files, starting in the dirname directory.
func LoadConfig(dirname string) (*Config, error) {
	cueConfig := &load.Config{
		Dir:        dirname,
	}

	buildInstances := load.Instances([]string{}, cueConfig)
	runtimeInstances := cue.Build(buildInstances)
	instance := runtimeInstances[0]

	var config Config
	err := instance.Value().Decode(&config)
	if err != nil {
		return nil, err
	}
	return &config, nil
}

func main() {
	c, err := LoadConfig("config/")
	if err != nil {
		panic("error reading config")
	}
	//a struct foi preenchida com os valores
	fmt.Println(c.DB.Host)
}

```

One advantage of CUE's `package` concept is that we can break our configuration into smaller files, each with its own functionality. For example, inside the `config` directory, I split `config. Cue` into separate files:

_config/db.cue_

```go
package config

db: {
	user:     "db_user"
	password: "password"
	host:     "127.0.0.1"
	port:     3306
}
```

_config/metric.cue_

```go
package config

metric: {
	host: "http://localhost"
	port: 9091
}
```

_config/lang.cue_

```go
package config

langs: [
	"pt_br",
	"en",
	"es",
]
```

And it was not necessary to change anything in the `main.go` file for the settings to be loaded. With this, we can better separate the contents from the settings without impacting the application code.

## Conclusion

I just "scratched the surface" of what's possible with CUE in this post. It has been [attracting attention](https://twitter.com/kelseyhightower/status/1329620139382243328?s=61&t=mVll7YR0fRVtNeZLEVwKnA) and being adopted in projects such as [Istio](https://istio.io/), which it uses to generate OpenAPI `schemes` and CRDs for Kubernetes and [Dagger](https://docs.dagger.io/1215/what-is-cue/). It is a tool that can be very useful for several projects, mainly due to its data validation power. And as a replacement for YAML, for my personal joy :D
