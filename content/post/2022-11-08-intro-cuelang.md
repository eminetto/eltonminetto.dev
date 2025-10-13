---
title: "Introdução a Cuelang"
date: 2022-11-08T13:00:19-03:00
draft: false
tags:
  - go
---

Aposto que nesse momento uma frase paira na sua cabeça:

> "Mais uma linguagem de programação"?

Calma, calma, vem comigo que vai fazer sentido :)

Diferente de outras linguagens como Go ou Rust, que são de "propósito geral", a [CUE](https://cuelang.org) possui alguns propósitos bem específicos. O seu nome na verdade é uma sigla que significa "Configure Unify Execute" e segundo a documentação oficial:

> Embora a linguagem não seja uma linguagem de programação de uso geral, ela possui muitas aplicações, como validação e modelagem de dados, configuração, consulta, geração de código e até script.

Ela é descrita como um "superset de JSON" e fortemente inspirada em Go. Ou como eu gosto de pensar:

> "imagine que Go e JSON tiveram um tórrido romançe e o fruto dessa união foi CUE" :D

Neste post eu vou apresentar dois cenários onde a linguagem pode ser usada, mas a [documentação oficial](https://cuelang.org/docs/) tem mais exemplos e uma boa quantia de informação importante a ser consultada.

## Validando dados

O primeiro cenário onde CUE se destaca é na validação de dados. Ela possui [suporte nativo](https://cuelang.org/docs/integrations/) para validar YAML, JSON, Protobuf, entre outros.

Vou usar como case alguns exemplos de [arquivos de configuração](https://doc.traefik.io/traefik/user-guides/crd-acme/) do projeto Traefik, um API Gateway.

O YAML a seguir define uma rota válida para o Traefik:

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

Com essa informação é possível definirmos uma nova rota no API Gateway, mas se algo estiver errado podemos causar alguns problemas. Por isso é importante termos uma forma fácil de detectarmos problemas em arquivos de configuração como esse. E é aí que a CUE mostra sua força.

O primeiro passo é termos a linguagem instalada na máquina. Como estou usando macOS bastou executar o comando:

```bash
brew install cue-lang/tap/cue
```

Na [documentação](https://cuelang.org/docs/install/) oficial é possível ver como fazer a instalação em outros sistemas operacionais.

Agora podemos usar o comando `cue` para transformar esse YAML em um `schema` da linguagem CUE:

```bash
cue import traefik-simple.yaml
```

É criado um arquivo chamado `traefik-simple.cue` com o conteúdo:

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

Ele é uma tradução literal do YAML para CUE, mas vamos editá-lo para criarmos algumas regras de validação. O conteúdo final do `traefik-simple.cue` ficou desta forma:

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

Alguns dos itens ficaram exatamente iguais, como `apiVersion: "traefik.containo.us/v1alpha1"` e `kind: "IngressRoute"`. Isso significa que esses são os valores exatos que estão esperados em todos os arquivos que serão validados por esse `schema`. Qualquer valor diferente destes vai ser considerado um erro. Outras informações foram alteradas, como:

```go
metadata: {
	name:      string
	namespace: string
}
```

Neste trecho definimos que o conteúdo do `name`, por exemplo, pode ser qualquer `string` válida. No trecho `port: >0 & <= 65535` estamos fazendo uma validação importante ao definir que este campo só pode aceitar um número que seja maior do que 0 e menor ou igual a 65535.

Agora é possível validar se o conteúdo do YAML está de acordo com o `schema` usando o comando:

```bash
cue vet traefik-simple.cue traefik-simple.yaml
```

Se tudo estiver correto nada é apresentado na linha de comando. Para demonstrar o funcionamento eu fiz uma alteração no `traefik-simple.yaml` mudando o valor do `port` para `0`. Ao executar o comando novamente é possível ver o erro:

```bash
cue vet traefik-simple.cue traefik-simple.yaml
spec.routes.0.services.0.port: invalid value 0 (out of bound >0):
    ./traefik-simple.cue:16:10
    ./traefik-simple.yaml:14:18
```

Se alterarmos algum dos valores esperados, como por exemplo `kind: IngressRoute` para algo diferente, como `kind: Ingressroute` o resultado é um erro de validação:

```go
cue vet traefik-simple.cue traefik-simple.yaml
kind: conflicting values "IngressRoute" and "Ingressroute":
    ./traefik-simple.cue:2:13
    ./traefik-simple.yaml:2:8
```

Desta forma é muito fácil encontrar algum erro em uma configuração de rotas do Traefik. O mesmo pode ser aplicado para outros formatos como JSON, Protobuf, arquivos do Kubernetes, etc.

Vejo um cenário muito claro de uso desse poder de validação de dados: adicionar um passo em CI/CDs para usar CUE e validar configurações em tempo de `build`, evitando problemas em `deploy` e execução de aplicações. Outro cenário é adicionar os comandos em um `hook` de Git, para validar as configurações ainda em ambiente de desenvolvimento.

Outra característica interessante da CUE é a possibilidade de criarmos `packages`, que contém uma série de `schemas` e que podem ser compartilhados entre projetos, da mesma forma que um `package` de Go. Na [documentação oficial](https://cuelang.org/docs/concepts/packages/#packages) é possível ver como user esse recurso, bem como usar alguns `packages` [nativos](https://cuelang.org/docs/concepts/packages/#builtin-packages) da linguagem, como `strigs`, `lists`, `regex` etc. Vamos usar um `package` no próximo exemplo.

## Configurando aplicações

Outro cenário de uso da CUE é como linguagem de configuração de aplicações. Quem me conhece sabe que eu não tenho nenhum apreço por YAML (para dizer o mínimo) então qualquer outra opção chama minha atenção. Mas CUE tem algumas vantagens interessantes como:

- por ser baseado em JSON torna a leitura e escrita muito mais simples (opinião minha)
- resolve alguns problemas de JSON como a falta de comentários, o que é uma vantagem para YAML
- por ser uma linguagem completa, é possível usar `if`, `loop`, pacotes embutidos na linguagem, herança de tipos, etc.

Para este exemplo o primeiro passo foi criar um pacote para armazenar nossa configuração. Para isso criei um diretório chamado `config` e dentro dele um arquivo chamado `config.cue` com o conteúdo:

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

O próximo passo foi criar a aplicação que faz a leitura da configuração:

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

Uma vantagem do conceito de `package` da CUE é que podemos quebrar a nossa configuração em arquivos menores, cada um com sua funcionalidade. Por exemplo, dentro do diretório `config` eu dividi o `config.cue` em arquivos distintos:

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

E não foi necessário alterar nada no arquivo `main.go` para que as configurações sejam carregadas. Com isso podemos ter uma separação melhor dos conteúdos das configurações, sem impacto no código da aplicação.

## Conclusão

Neste post eu apenas "arranhei a superfície" do que é possível fazer com a CUE. Ela vem [chamando atenção](https://twitter.com/kelseyhightower/status/1329620139382243328?s=61&t=mVll7YR0fRVtNeZLEVwKnA) e sendo adotada em projetos importantes como o [Istio](https://istio.io/), que usa para gerar `schemes` OpenAPI e CRDs para Kubernetes, e o [Dagger](https://docs.dagger.io/1215/what-is-cue/). Me parece uma ferramenta que pode ser muito útil para uma série de projetos, em especial devido ao seu poder de validação de dados. E como um substituto para YAML, para minha alegria pessoal :D
