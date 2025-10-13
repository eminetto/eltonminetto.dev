---
title: Alternatives to Makefiles written in Go
date: 2024-05-26T08:00:43-03:00
draft: false
---
First things first: what is `make`? Present in all Linux distributions and Unix derivatives such as macOS, the tool's manual describes it as:

> The purpose of the make utility is to determine automatically which pieces of a large program need to be recompiled, and issue the commands to recompile them.

> To prepare to use make, you must write a file called the Makefile that describes the relationships among files in your program, and the states the commands for updating each file.


Before anyone throws stones at me, I like it, and practically every project I build has one `Makefile` with automation to make my work easier.

But then, why look for alternatives to something that has existed and worked for decades? Learning new tools is part of our job as developers and keeps us up to date with new forms of automation. Furthermore, to start using it, we must learn the syntax of the `Makefile`, and if we can use something we already know, it can reduce the cognitive load of new professionals.

Let's look at two alternatives here, both written in Go.

# Taskfile

The first tool we will test is  `Taskfile`, found on the website [https://taskfile.dev/](https://taskfile.dev/). The tool's idea is to perform tasks described in a file called `Taskfile.yaml` and, as the name suggests, in `yaml`.

The first step is to install the executable `task`, which we will use. For this, the official documentation shows some alternatives, but as I'm using macOS, I used the command:

```bash
❯ brew install go-task
```

Let's describe our tasks in a new `Taskfile.yaml` file. Let's rewrite one Makefile from a [project on my Github](https://github.com/eminetto/api-o11y-gcp) to demonstrate a real case.

The original content is:

```Makefile
.PHONY: all
all: build
FORCE: ;

.PHONY: build

build:
	go build -o bin/api-o11y-gcp cmd/api/main.go

build-linux:
	CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -tags "netgo" -installsuffix netgo -o bin/api-o11y-gcp cmd/api/main.go

build-docker: 
	docker build -t api-o11y-gcp -f Dockerfile .

generate-mocks:
	@mockery --output user/mocks --dir user --all
	@mockery --output internal/telemetry/mocks --dir internal/telemetry --all

clean:
	@rm -rf user/mocks/*
	@rm -rf internal/telemetry/mocks/mocks/*

test: generate-mocks
	go test ./...

run-docker: build-docker
    docker run -d -p 8080:8080 api-o11y-gcp
```

The content converted to the `Taskfile.yaml` is:

```yaml
version: "3"

tasks:
  install-deps:
    cmds:
      - go mod tidy

  default:
    desc: "Build the app"
    deps: [install-deps]
    cmds:
      - go build -o bin/api-o11y-gcp cmd/api/main.go

  build-linux:
    deps: [install-deps]
    desc: "Build for Linux"
    cmds:
      - go build -a -installsuffix cgo -tags "netgo" -installsuffix netgo -o bin/api-o11y-gcp cmd/api/main.go
    env:
      CGO_ENABLED: 0
      GOOS: linux

  build-docker:
    desc: "Build a docker image"
    cmds:
      - docker build -t api-o11y-gcp -f Dockerfile .

  generate-mocks:
    desc: "Generate mocks"
    cmds:
      - go install github.com/vektra/mockery/v2@v2.43.1
      - mockery --output user/mocks --dir user --all
      - mockery --output internal/telemetry/mocks --dir internal/telemetry --all

  test:
    deps:
      - install-deps
      - generate-mocks
    desc: "Run tests"
    cmds:
      - go test ./...

  clean:
    desc: "Clean up"
    prompt: This is a dangerous command... Do you want to continue?
    cmds:
      - rm -f bin/*
      - rm -rf user/mocks/*
      - rm -rf internal/telemetry/mocks/mocks/*

  run-docker:
    desc: "Run the docker image"
    deps: [build-docker]
    cmds:
      - docker run -d -p 8080:8080 api-o11y-gcp

```

We can now use the command `task` to list the available tasks:

```bash
❯ task -l
task: Available tasks for this project:
* build-docker:         Build a docker image
* build-linux:          Build for Linux
* clean:                Clean up
* default:              Build the app
* generate-mocks:       Generate mocks
* run-docker:           Run the docker image
* test:                 Run tests
```

When executing the command `task`, it will perform the `default` task:

```bash
❯ task
task: [install-deps] go mod tidy
task: [default] go build -o bin/api-o11y-gcp cmd/api/main.go
```

You can see that the task first executed its dependency, `install-deps`, as described in `Taskfile.yaml`.

And we can perform other tasks by adding it to the end of the command:

```bash
❯ task build-linux
task: [install-deps] go mod tidy
task: [build-linux] go build -a -installsuffix cgo -tags "netgo" -installsuffix netgo -o bin/api-o11y-gcp cmd/api/main.go
```

The command `build-linux` also shows the use of `environment variables` to configure the environment at compilation time.

The [documentation](https://taskfile.dev/usage/) includes other, more advanced examples and a style guide for writing a `Taskfile.yaml.`


The main advantage of using `Taskfile` is that most teams nowadays have experience writing and using files in `YAML`, which has become the most used format for configuration files (although I think the [TOML](https://toml.io/en/) format is much better ).

## Mage

The second alternative I want to demonstrate is the [Mage](https://magefile.org/) project, which the site describes as

> a make/rake-like build tool using Go

The exciting thing about this tool is that the tasks are built in Go files, giving them all the power the language provides.

The first necessary step is to install the executable `mage`. To do this, I used the following command on macOS, but you can view the options for other operating systems on the official website.

```bash
❯ brew install mage
```

Let's rewrite the tasks in `Makefile` in this new format. To do this, we can create a file called `magefile.go` at the project's root and add the logic inside it. However, another documented option is more interesting: creating a directory called `magefiles` and storing the files within it. I thought the project was more organized this way. To do this, I ran the commands:

```bash
❯ mkdir magefiles
❯ mage -init -d magefiles
```

The second command initializes a `magefile.go` with an initial example to begin describing the tasks:

```go
//go:build mage
// +build mage

package main

import (
	"fmt"
	"os"
	"os/exec"

	"github.com/magefile/mage/mg" // mg contains helpful utility functions, like Deps
)

// Default target to run when none is specified
// If not set, running mage will list available targets
// var Default = Build

// A build step that requires additional params, or platform specific steps for example
func Build() error {
	mg.Deps(InstallDeps)
	fmt.Println("Building...")
	cmd := exec.Command("go", "build", "-o", "MyApp", ".")
	return cmd.Run()
}

// A custom install step if you need your bin someplace other than go/bin
func Install() error {
	mg.Deps(Build)
	fmt.Println("Installing...")
	return os.Rename("./MyApp", "/usr/bin/MyApp")
}

// Manage your deps, or running package managers.
func InstallDeps() error {
	fmt.Println("Installing Deps...")
	cmd := exec.Command("go", "get", "github.com/stretchr/piglatin")
	return cmd.Run()
}

// Clean up after yourself
func Clean() {
	fmt.Println("Cleaning...")
	os.RemoveAll("MyApp")
}

```

As we will describe the tasks in the form of a Go program, it is necessary to download the dependency using the command:

```bash
❯ go get github.com/magefile/mage/mg
```

Now it is possible to list the available tasks, which `Mage` calls `targets`:

```bash
❯ mage -l
Targets:
  build          A build step that requires additional params, or platform specific steps for example
  clean          up after yourself
  install        A custom install step if you need your bin someplace other than go/bin
  installDeps    Manage your deps, or running package managers.
```

Each function's comment line becomes a documentation of how we can view the command in the `mage` output message.

Let's now convert the `Makefile` into a script in the `mage` format:

```go
//go:build mage
// +build mage

package main

import (
	"log"
	"os"
	"os/exec"
	"path/filepath"

	"github.com/magefile/mage/mg" // mg contains helpful utility functions, like Deps
)

// Default target to run when none is specified
// If not set, running mage will list available targets
var Default = Build

// A build step that requires additional params, or platform specific steps for example
func Build() error {
	mg.Deps(InstallDeps)
	log.Println("Building...")
	cmd := exec.Command("go", "build", "-o", "bin/api-o11y-gcp", "cmd/api/main.go")
	return cmd.Run()
}

// Build for Linux
func BuildLinux() error {
	mg.Deps(InstallDeps)
	log.Println("Generating Linux binary...")
	os.Setenv("CGO_ENABLED", "0")
	os.Setenv("GOOS", "linux")
	cmd := exec.Command("go", "build", "-a", "-installsuffix", "cgo", "-tags", `"netgo"`, "-installsuffix", "netgo", "-o", "bin/api-o11y-gcp", "cmd/api/main.go")
	return cmd.Run()
}

// Build a docker image
func BuildDocker() error {
	log.Println("Building...")
	cmd := exec.Command("docker", "build", "-t", "api-o11y-gcp", "-f", "Dockerfile", ".")
	return cmd.Run()
}

// Generate mocks
func GenerateMocks() error {
	log.Println("Installing mockery...")
	cmd := exec.Command("go", "install", "github.com/vektra/mockery/v2@v2.43.1")
	err := cmd.Run()
	if err != nil {
		return err
	}
	log.Println("Generating user mocks...")
	cmd = exec.Command("mockery", "--output", "user/mocks", "--dir", "user", "--all")
	err = cmd.Run()
	if err != nil {
		return err
	}
	log.Println("Generating telemetry mocks...")
	cmd = exec.Command("mockery", "--output", "internal/telemetry/mocks", "--dir", "internal/telemetry", "--all")
	return cmd.Run()
}

// Manage your deps, or running package managers.
func InstallDeps() error {
	log.Println("Installing Deps...")
	cmd := exec.Command("go", "mod", "tidy")
	return cmd.Run()
}

// Run tests
func Test() error {
	mg.Deps(GenerateMocks)
	cmd := exec.Command("go", "test", "./...")
	return cmd.Run()
}

// Run the docker image
func RunDocker() error {
	mg.Deps(BuildDocker)
	cmd := exec.Command("docker", "run", "-p", "8080:8080", "api-o11y-gcp")
	return cmd.Run()
}

// Clean up after yourself
func Clean() error {
	log.Println("Cleaning...")
	err := removeGlob("user/mocks/*")
	if err != nil {
		return err
	}
	err = removeGlob("internal/telemetry/mocks/*")
	if err != nil {
		return err
	}
	return os.RemoveAll("bin/api-o11y-gcp")
}

func removeGlob(path string) (err error) {
	contents, err := filepath.Glob(path)
	if err != nil {
		return
	}
	for _, item := range contents {
		err = os.RemoveAll(item)
		if err != nil {
			return
		}
	}
	return
}
```

In this file, you can see the use of dependencies, as in the example `mg.Deps(BuildDocker)`. You can also see the use of Go programming logic, such as in the `removeGlob(path string)`. This function could, for example, be in a separate package and used by different files within the directory `magefiles`, using suitable language practices.

We can now view all `targets` available:

```bash
❯ mage -l
Targets:
  build*           A build step that requires additional params, or platform specific steps for example
  buildDocker      Build a docker image
  buildLinux       Build for Linux
  clean            up after yourself
  generateMocks    Generate mocks
  installDeps      Manage your deps, or running package managers.
  runDocker        Run the docker image
  test             Run tests

* default target
```

When executing the `mage` command, the function indicated as `Default` will be executed, in this case the `build`:


```bash
❯ mage

❯ mage -v
Running dependency: InstallDeps
Installing Deps...
Building...
```

In the second execution, the result is more detailed when we add the flag `-v`, as we can see in the logs.

I see two advantages of using `mage` in a project. The first is that if the project is written in Go, the team does not need to learn a new language to describe the automated tasks. The second benefit is that we have a complete programming language, not just commands defined in a `Makefile` or `Taskfile.yaml` file. This power allows us to execute complex logic more easily (I've seen giant `Makefile` files with unfriendly syntax to get around this need).


## Conclusions

`Make` is a mature tool used by all the main Open Sorce projects worldwide, and this is not likely to change so quickly. That's why it's very valid that knowledge of this tool is encouraged among devs. However, adding alternatives like the ones presented here can be a crucial step in facilitating the creation of tasks and automation, thanks to the advantages I mentioned in the text.

Do you know of other alternatives? Do you disagree with adopting something other than `make`? I shared your opinions and experiences in the comments.

