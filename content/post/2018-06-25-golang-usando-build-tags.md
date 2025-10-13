---
title: "Golang: usando build tags para armazenar configurações"
subtitle: ""
date: "2018-06-25T08:54:24+02:00"
bigimg: ""
tags:
  - go
---

Um dos [12 fatores](http://12factor.net), conjunto de boas práticas muito usado em projetos modernos, é:

> Armazene as configurações no ambiente

<!--more-->

Realmente atender a esta prática deixa torna o projeto muito mais maleável, mas um dos requisitos para isso é que você tenha o controle do ambiente onde a sua aplicação vai executar. Isso é verdadeiro quando pensamos em fazer o _deploy_ de uma aplicação web, api ou micro serviço. Mas e quando o nosso aplicativo precisa executar na máquina de um usuário final, onde não temos o controle do ambiente? Passei por esta situação recentemente, ao desenvolver um aplicativo _CLI_ para ser usado pelos usuários da [Code:Nation](http://codenation.com.br).

Como estamos usando Go como principal linguagem de programação a solução escolhida foi o uso das _build tags_, que são condições passadas para o compilador e que são usadas no momento da geração do binário do projeto.

Para isso, criamos um pacote chamado _config_ onde armazenamos as configurações do projeto, separadas pelos ambientes onde ele vai executar:

[![config_tree.png](/images/posts/config_tree.png)](/images/posts/config_tree.png)

Dentro de cada arquivo criamos constantes com as configurações necessárias:

[![config_dev.png](/images/posts/config_dev.png)](/images/posts/config_dev.png)

O detalhe importante é a primeira linha do arquivo, onde consta:

    // +build dev

Esta é a _build tag_ que vamos usar no momento da compilação:

    go build -tags dev -o ./bin/api api/main.go
    go build -tags dev -o ./bin/search cmd/main.go

Desta forma o compilador vai ignorar os outros arquivos, que possuem uma _tag_ diferente da indicada. Os arquivos que não possuem _tags_ são processados normalmente durante a compilação, então não é preciso alterar mais nada no projeto.

Para fazer uso das configurações basta importar o pacote normalmente, como no exemplo:

[![config_main.png](/images/posts/config_main.png)](/images/posts/config_main.png)

Usando _build tags_, junto com ferramentas de automação como _make_ e o excelente [GoReleaser](https://goreleaser.com/) podemos facilitar muito o processo de _build_ e _deploy_ de aplicativos escritos em Go.

Se você quiser ler mais sobre as _build tags_ uma boa dica é a [documentação oficial](https://golang.org/pkg/go/build/). E se quiser ver o exemplo completo que mostrei neste post o código está no [Github](https://github.com/eminetto/clean-architecture-go).
