---
title: "Mini-FAQ sobre Go"
date: 2024-01-02T08:00:43-03:00
draft: false
tags:
  - go
---

Alguns dias atrás o [@met4tron](https://twitter.com/async_http) teve uma ideia bem interessante: ele [postou no X](https://x.com/async_http/status/1736933828743020890?s=20) uma lista de perguntas sobre Go.

Gostei tanto da ideia que resolvi transformar minhas respostas neste mini-FAQ (Frequently Asked Question). Recomendo a leitura do [documento original](https://gist.github.com/Met4tron/395a7380a9ccd6b8c97e2541d16f06b1) para conferir as respostas de outras pessoas.

## Motivos

1. Porque aprender Golang e não Rust?

> Eu não vejo Rust e Go como concorrentes. Go tem sido usado bastante em microsserviços e APIs, aplicações dentro de empresas. Já Rust tem sido usado mais para coisas "baixo nível", como IDEs ([Zed](https://zed.dev/)), terminais ([Warp](https://www.warp.dev/)) e até no kernel do Linux e do Windows. Eu faço uma analogia com Go substituindo aplicações que foram escritas em Java e Rust substituindo coisas que foram originalmente feitas em C/C++. Ou seja: estude os dois (é o que eu pretendo fazer)

2. Porque aprender Golang e não JS/TS?

> Com Go você gera aplicações nativas para o sistema operacional que vai ser usado, o que resulta em uma ótima performance. Por ser uma linguagem compilada tem muita coisa que você consegue detectar já na compilação, diferente de JS (não usei TS o suficiente para poder dizer o quão legal é).

3. O quão custoso é a simplicidade do Golang?

> Eu não vejo custos nesse caso. Simplicidade é uma das melhores coisas da linguagem :)

4. Quais os pontos negativos do Golang?

> Menos vagas do que outras linguagens mais antigas. Menos livros e materiais em portugues, apesar disso ter melhorado muito nos últimos anos.

5. Por onde começar?

> Respondi essa [neste post](https://eltonminetto.dev/post/2019-10-08-golang-por-onde-comecar/).

## Mercado

1. Quais os principais players/empresas que utilizam Golang no BR?

> De cabeça eu lembro de Globo, PicPay, Transfeera, ContaAzul, LuizaLabs, Magalu Cloud, Resultados Digitais, Pismo. Neste [link](https://go.dev/wiki/GoUsers) tem mais cases.

2. Existem vagas mid/senior? Só vejo vaga SR+

> Eu tenho uma teoria quanto a isso: na maioria das vezes as empresas começam a usar Go quando estão enfrentando problemas de performance, concorrência, escalabilidade. Nestes cenários o normal é procurar pessoas que tem mais experiência, por isso tem mais vagas para senior+ do que outras linguagens como JS. A tendência é isso melhorar com o tempo, conforme mais empresas começam a adotar Go como sua linguagem principal.

3. Existem vagas para Backend, sem que envolva infraOps?

> Sim, muitas empresas usam Go para desenvolver APIs e microsserviços.

4. Há perspectiva de crescimento de demanda/vagas para Golang?

> Não tenho números para provar isso, mas tenho visto um aumento nas vagas. Um contra-ponto é que os salários tendem a ser maiores.

## Idioms

1. É preferivel utilizar http (standard) ou algum framework/router?

> A tendência da comunidade é usar ao máximo a stdlib, para garantir a compatibilidade com as futuras versões da linguagem. Eu gosto de libs pequenas, que adicionam algumas funcionalidades mas mantém a compatibilidade com a stdlib, como o [Chi](https://go-chi.io/#/). Mas na versão 1.22 está prevista uma [melhoria grande na lib http](https://eli.thegreenplace.net/2023/better-http-server-routing-in-go-122/) que vai tornar obsoleta várias libs de terceiros.

2. Injeção de dependencias via reflector ou instanciando tudo na mão? (Wire ou Dig)

> Eu prefiro usar o main.go como o local para definir e invocar as dependências, passando elas explicitamente para os serviços (ou como queira chamar) que vão usá-las. Explicito é sempre melhor que implícito. Isso torna mais fácil os testes também.

4. Quais o padrões de códigos são comuns em Golang?

> Esta [documentação](https://go.dev/doc/effective_go) tem muita coisa importante que é recomendado ser seguido. Algumas empresas tem criado padrões baseados neste, mas simplificado ou adaptado para suas realidades. É o que fizemos no [PicPay](https://medium.com/inside-picpay/organizing-projects-and-defining-names-in-go-7f0eab45375d).

5. Caso tenha migrado para golang de uma linguagem POO, como foi a adaptação?

> Eu vim de PHP e as únicas coisas que eu estranhei no começo foi o tratamento de erros e a falta de herança. Mas logo entendi como funcionam e não consigo me ver usando outra coisa :)

## Padrões

1. Como voce organizaria uma API REST modular com Golang?

> Esta [documentação](https://medium.com/inside-picpay/organizing-projects-and-defining-names-in-go-7f0eab45375d) representa o que eu uso hoje para organizar um projeto em Go. Além disso, gravei um [curso](https://eltonminetto.dev/post/2021-03-32-curso-go/) com um exemplo de API. Ele está um pouco antigo mas acredito que ainda seja válido (pretendo revisar esses videos em 2024)

2. Quais arquiteturas são mais usadas em Golang?

> Acredito que sejam Clean Architecture e Hexagonal (ou ports/adapters)

3. Quais são as principais libs usadas no dia a dia?

> Complicado listar porque são muitos projetos diferentes. Minha recomendação é sempre procurar no [Awesome Go](https://awesome-go.com/)

Sentiu falta de alguma pergunta? Use os comentários para adicionar sua dúvida que eu atualizo o post com as minhas respostas.
