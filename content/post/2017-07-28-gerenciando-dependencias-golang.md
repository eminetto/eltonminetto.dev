---
title: "Gerenciando dependências em Golang"
subtitle: ""
date: "2017-07-28T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

Desenvolver software atualmente se parece cada vez mais com montar um quebra-cabeça. Foi-se o tempo dos grandes frameworks, com várias funcionalidades acopladas, e é muito comum usarmos vários pequenos pacotes para montarmos nossos projetos.

<!--more-->

A maioria das linguagens de programação modernas possui um sistema de gerenciamento destas dependências, como o Composer do PHP, o yarn/npm para JavaScript, pip no Python, etc. No ecossistema Go existem várias implementações deste conceito, o que acabou gerando um certo conflito na hora de selecionar uma solução. Para resolver isso a comunidade começou a desenvolver uma ferramenta para ser o padrão das próximas versões da linguagem.

Esta ferramenta é o [dep](https://github.com/golang/dep) que está em acelerado desenvolvimento. Apesar de ainda não estar [pronto](https://github.com/golang/dep/wiki/Roadmap) ele já pode ser considerado "safe for production use", segundo o site oficial.

Neste post vou mostrar como usá-lo em um [projeto já existente](https://github.com/eminetto/goCep), o primeiro que eu desenvolvi em Go.

O primeiro passo é instalar a ferramenta, usando o comando:

    go get -u github.com/golang/dep/cmd/dep

Depois basta entrar no projeto e executar o comando:

    cd goCep
    dep init

A saída do comando foi:

    Using ^1.4.0 as constraint for direct dep github.com/gorilla/mux
    Locking in v1.4.0 (bcd8bc7) for direct dep github.com/gorilla/mux
    Locking in v1.1 (1ea2538) for transitive dep github.com/gorilla/context
    Using master as constraint for direct dep github.com/andelf/go-curl
    Locking in master (f8b334d) for direct dep github.com/andelf/go-curl
    Using master as constraint for direct dep github.com/ryanuber/go-filecache
    Locking in master (52ce07f) for direct dep github.com/ryanuber/go-filecache

O que o _dep_ fez foi analisar os meus arquivos _.go_ procurando pelos _import_ e ao encontrá-lo ele fez os seguintes passos:

- criou um diretório chamado _vendor_
- fez o _go get_ de cada dependência salvando os arquivos no _vendor_
- criou um arquivo chamado _Gopkg.toml_ com as definições das dependências
- criou um arquivo chamado _Gopkg.lock_ com os detalhes das versões instaladas, incluindo o _commit_ específico que está sendo usado de cada dependência

Para quem está usando o _Composer_ do PHP vai reconhecer alguns destes passos pois o resultado é parecido com o _composer.json_ e _composer.lock_.

Agora basta salvar no repositório os arquivos _Gopkg.toml_ e _Gopkg.lock_. [Alguns projetos](https://github.com/digitalocean/doctl) defendem a ideia de salvar no repositório o diretório _vendor_ para facilitar a compilação, mas eu não acho uma alternativa muito válida. Prefiro salvar apenas os arquivos das definições das dependências e não o código delas.

Para instalar as dependências novamente basta ter o _dep_ instalado e executar:

    dep ensure

Quando uma nova dependência for necessária no projeto basta adicionar ela no código usando o _import_ e executar novamente o _dep ensure_ que ela será instalada.

O _dep_ possui mais algumas funcionalidades que podem ser vistas no site oficial, como compatibilidade com outros gerenciadores (Glide por exemplo), atualização das dependências, configurações do arquivo _.toml_, etc.

O plano é a ferramenta ser incluída por padrão em todas as instalações da linguagem a partir da versão 1.10, o que vai facilitar ainda mais o uso no dia a dia. Sem dúvida vai ser uma ótima adição a linguagem.
