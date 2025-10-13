+++
title = "Processando JSON com o comando jq"
subtitle = ""
date = "2017-08-09T08:10:24-03:00"
bigimg = ""
+++

Nos últimos anos *JSON* tornou-se o "esperanto" da Internet, sendo o padrão para a comunicação de APIs e serviços. Por isso processar *JSON* é algo que os desenvolvedores fazem diariamente e qualquer ferramenta que facilite esse processo é sempre bem-vinda. Uma destas ferramentas é o *jq*, que vou apresentar neste post.

<!--more-->

O [jq](https://stedolan.github.io/jq/) é uma ferramenta de linha de comando e que pode ser instalada nos principais sistemas operacionais. Como estou usando MacOS instalei usando o comando:

```bash
brew install jq
```

No site oficial é possível ver a documentação para instalação em outros ambientes. Com o *jq* instalado podemos fazer nossos primeiros testes. Para isso vamos usar o seguinte arquivo *JSON*:

https://gist.github.com/eminetto/0960739bab52ed4d50d8c6262ec43893

Podemos usar o *jq* para formatar o arquivo e mostrá-lo colorido em nosso terminal:

```bash
cat cr.json| jq
```

Mas o *jq* vai além disso. Podemos fazer consultas no arquivo, como por exemplo trazer apenas a informação que desejamos:

```bash
cat cr.json| jq .reports[].maintainability
```

Ele fez uma pesquisa no conteúdo e trouxe apenas os valores encontrados:

```bash
124.63257712933509
128.03015432120054
```

Podemos também fazer operações sobre estes conteúdos. Por exemplo, vamos somar os dois valores:

```bash
cat cr.json| jq '[.reports[].maintainability] | add'
```

A primeira alteração foi incluir os caracteres **[** e **]** ao redor da nossa pesquisa, transformando o seu conteúdo em um *array*. Isso é necessário pois o comando **add**, nativo do *jq*, faz a soma de valores dentro de um *array*. O resultado foi:

```bash
252.66273145053563
```

Podemos expandir um pouco mais nosso exemplo calculando a média dos valores:

```bash
cat cr.json| jq '[.reports[].maintainability] | add / length'
```

Este é somente um exemplo simples mas que economizou várias linhas de programação de um [projeto que estou trabalhando](http://codenation.com.br). Outro exemplo um pouco mais complexo:

```bash
cat codeclimate.json | jq -c '[.[] | select(.check_name | contains("Complexity") | not)]' | jq 'map(.remediation_points) | add'
```

Não vou entrar em detalhes neste exemplo para não alongar demais o post, mas a própria documentação do *jq* ajuda bastante:

```bash
man jq
```

O *jq* rapidamente tornou-se uma das minhas ferramentas favoritas e espero que ajude mais desenvolvedores.