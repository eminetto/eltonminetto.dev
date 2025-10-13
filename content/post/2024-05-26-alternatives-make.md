---
title: Alternativas ao make escritas em Go
date: 2024-05-26T08:00:43-03:00
draft: false
tags:
  - go

---
Começando do começo: o que é o `make`? Presente em todas as distribuições Linux e derivados do Unix como o macOS, o manual da ferramenta a descreve como:

> O objetivo do utilitário make é determinar automaticamente quais partes de um programa grande precisam ser recompiladas, e emitir os comandos para recompilá-los.

> Para se preparar para usar o make, você deve escrever um arquivo chamado makefile que descreve os relacionamentos entre os arquivos em seu programa e indica os comandos para atualizar cada arquivo.

Antes que me atirem pedras, eu gosto muito do `make` e praticamente todo projeto que eu construo tem um `Makefile` com automações para facilitar o meu trabalho.

Mas então porque procurar alternativas a algo que existe e funciona há décadas? Acredito que aprender novas ferramentas faz parte do nosso trabalho como devs, além de nos manter atualizados de novas formas de automação. Além disso, para começar a usar o `make` é preciso aprender a sintaxe do `Makefile` e se pudermos usar algo que já conhecemos pode diminuir a carga cognitiva de novos profissionais.

Dito isso, vamos ver aqui duas alternativas, ambas escritas em Go.

# Taskfile

A primeira ferramenta que vamos testar chama-se `Taskfile` e pode ser encontrada no site [https://taskfile.dev/](https://taskfile.dev/). A ideia da ferramenta é executar tarefas descritas em um arquivo chamado `Taskfile.yaml` e, como o nome sugere, em formato `yaml`. 

O primeiro passo é realizar a instalação do executável `task`, que vamos utilizar. Para isso a documentação oficial mostra algumas alternativas, mas como estou usando macOS eu usei o comando:

```bash
❯ brew install go-task
```

Vamos agora descrever as nossas tarefas dentro de um novo arquivo chamado `Taskfile.yaml`. Para demonstrar um caso real, vamos reescrever o `Makefile` de um [projeto do meu Github](https://github.com/eminetto/api-o11y-gcp).

Este é o conteúdo original:

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

O conteúdo do `Taskfile.yaml` ficou desta forma:

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

Podemos agora usar o comando `task` para listar as tarefas disponíveis:

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

Ao executar o comando `task` a tarefa `default` vai ser executada:

```bash
❯ task
task: [install-deps] go mod tidy
task: [default] go build -o bin/api-o11y-gcp cmd/api/main.go
```

É possível ver que a tarefa executou primeiro a sua dependência, o `install-deps`, conforme descrito no `Taskfile.yaml`. 

E podemos executar outras tarefas adicionando o seu nome ao final do comando:

```bash
❯ task build-linux
task: [install-deps] go mod tidy
task: [build-linux] go build -a -installsuffix cgo -tags "netgo" -installsuffix netgo -o bin/api-o11y-gcp cmd/api/main.go
```

No comando `build-linux` é possível também ver a utilização de `env vars` para configurar o ambiente no momento da compilação.

Na [documentação](https://taskfile.dev/usage/) é possível ver outros exemplos mais avançados, além de um [guia de estilo](https://taskfile.dev/styleguide/) para escrever bons `Taskfile.yml`.

A principal vantagem em usar o `Taskfile` é que a grande maioria dos times atualmente tem experiência em escrever e usar arquivos no formato `YAML`, pois ele tornou-se o formato mais usado para arquivos de configuração (apesar de eu achar que o [formato TOML](https://toml.io/en/) é bem mais legal).

## Mage

A segunda alternativa que quero demonstrar é o projeto [Mage](https://magefile.org/) que se descreve como 

> uma ferramenta de construção semelhante a make/rake usando Go

O interessante desta ferramenta é que as tarefas são construídas em arquivos Go, com todo o poder que a linguagem nos fornece.

O primeiro passo necessário é a instalação do executável `mage`. Para isso usei o comando a seguir no macOS, mas no site oficial é possível visualizar as opções para outros sistemas operacionais.

```bash
❯ brew install mage
```

Vamos novamente reescrever as tarefas do `Makefile` neste novo formato. Para isso podemos criar um arquivo chamado `magefile.go` na raiz do projeto e adicionar a lógica dentro dele. Mas eu achei mais interessante outra opção documentada, a de criarmos um diretório chamado `magefiles` e dentro dele armazenar os arquivos. Achei que desta forma o projeto fica mais organizado. Para isso executei os comandos:

```bash
❯ mkdir magefiles
❯ mage -init -d magefiles
```

O segundo comando inicializa um arquivo `magefile.go` com um exemplo inicial para começarmos a descrever as tarefas:

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

Como as tarefas são descritas na forma de um script Go é necessário baixar a dependência do `Mage` usando o comando:

```bash
❯ go get github.com/magefile/mage/mg
```

Agora é possível listarmos as tarefas disponíveis, que o `Mage` chama de `targets`: 

```bash
❯ mage -l
Targets:
  build          A build step that requires additional params, or platform specific steps for example
  clean          up after yourself
  install        A custom install step if you need your bin someplace other than go/bin
  installDeps    Manage your deps, or running package managers.
```

A linha de comentário de cada função torna-se a documentação do `target` como é possível visualizarmos na saída do comando `mage`. 

Vamos agora converter o conteúdo do `Makefile` em um script no formato do `mage`:

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

Neste arquivo é possível ver o uso das dependências, como no exemplo: `mg.Deps(BuildDocker)`. Também é possível ver o uso de lógica de programação Go, como na função `removeGlob(path string)`. Esta função poderia, por exemplo, estar em um pacote separado e ser utilizado por diversos arquivos dentro do diretório `magefiles`, fazendo uso das boas práticas da linguagem.

Podemos agora visualizar todos os `targets` disponíveis: 

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

Ao executar o comando `mage` a função indicada como `Default` vai ser executada, neste caso a `build`:

```bash
❯ mage

❯ mage -v
Running dependency: InstallDeps
Installing Deps...
Building...
```

Na segunda execução, ao adicionarmos o flag `-v` o resultado é mais detalhado pois são apresentados os logs do `target`.

Vejo duas vantagens ao usar o `mage` em um projeto. O primeiro é que se o projeto é escrito em Go não torna-se necessário que o time aprenda uma nova linguagem para descrever as tarefas automatizadas. O segundo benefício é termos a disposição uma linguagem de programação completa e não apenas comandos descritos em um arquivo `Makefile` ou `Taskfile.yaml`. Isso permite a execução de lógicas complexas de maneira muito mais fácil (já vi arquivos `Makefile` gigantes, com uma sintaxe pouco amigável para contornar essa necessidade).

## Conclusões

O `make` é uma ferramenta madura e usada por todos os principais projetos Open Sorce do mundo, e isso não deve mudar tão facilmente. Por isso continuo achando muito válido que o conhecimento desta ferramenta seja incentivado entre devs. Mas adicionar alternativas como as apresentadas aqui pode ser um passo bem importante para facilitar a criação de tarefas e automações graças as vantagens que comentei no texto.

Conhece outras alternativas? Não concorda com a adoção de algo diferente do `make`? Compartilhei suas opiniões e experiências nos comentários.