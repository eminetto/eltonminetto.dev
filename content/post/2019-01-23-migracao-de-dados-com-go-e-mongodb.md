---
title: "Migração de dados com Golang e MongoDB"
subtitle: ""
date: "2019-01-23T10:09:24+02:00"
bigimg: ""
tags:
  - go
---

Um dos itens abordados pela metodologia [_twelve-factor app_](https://12factor.net) é a automatização de processos administrativos, como execução de scripts e migração de dados. É exatamente sobre isso que vou falar neste post: como automatizamos a migração de dados usando Go e MongoDB.

<!--more-->

Na [Code:Nation](https://www.codenation.com.br) escolhemos Go como linguagem de programação principal para o desenvolvimento do produto. Graças a esta escolha, e a adoção da [Clean Architecture](https://medium.com/@eminetto/clean-architecture-using-golang-b63587aa5e3f), conseguimos rapidamente criar _APIs, lambda functions_, aplicações de linha de comando (_CLI_), _bots_, etc. Podemos reaproveitar a lógica das camadas da _Clean Architecture_ para acelerar o desenvolvimento e evolução do produto.

Mas para o processo de migração de dados não havíamos encontrado uma forma simples de implementação em Go, por isso iniciamos usando uma [solução em node.js](https://www.npmjs.com/package/migrate-mongo).

A solução funcionou satisfatoriamente por vários meses, mas estávamos tendo pouca produtividade na criação dos scripts de migração. A principal razão era nossa falta de familiaridade com as nuances do _node.js_, principalmente o comportamento assíncrono das _queries_ executadas no _MongoDB_. E o fato de não podermos reaproveitar a lógica implementada em Go nos fazia _reinventar a roda_ em alguns momentos.

Então fizemos uma nova pesquisa e chegamos a uma solução em Go. O primeiro passo veio da descoberta deste projeto:

https://github.com/xakep666/mongo-migrate

Fizemos algumas [contribuições](https://github.com/xakep666/mongo-migrate/pull/1) no projeto e chegamos a uma solução que está funcionando bem para nós.

O primeiro passo foi a criação de um aplicativo _CLI_ que é responsável pela criação de novas migrações, bem como a execução das mesmas. O código deste aplicativo ficou desta forma:

[![main](/images/posts/migrations_main.png)](/images/posts/migrations_main.png)

Vamos começar criando uma nova migration, com o comando:

    go run cmd/migrations/main.go new alter-user-data

O resultado é algo como:

    2019/01/23 10:02:36 New migration created: ./migrations/20190123100236_alter-user-data.go

O que o comando fez foi copiar o arquivo `migrations/template.go` criando uma nova migração. Este é o conteúdo do `template.go`:

[![main](/images/posts/migrations_template.png)](/images/posts/migrations_template.png)

Podemos agora alterar este novo arquivo para executarmos os comandos necessários. Por exemplo:

[![main](/images/posts/migrations_migration.png)](/images/posts/migrations_migration.png)

Para executar as migrações basta:

    go run cmd/migrations/main.go up

E para desfazer a migração:

    go run cmd/migrations/main.go down

Ao executarmos o comando `up` a `collection migrations` é verificada para identificar qual foi a última migração efetivada. Automaticamente são executadas as que ainda estão pendentes, neste caso a `20190123090741_alter-user-data.go` e a `collection` é atualizada. Este é o comando que é executado durante o processo de _deploy_ de uma nova versão da aplicação.

O comando `down` faz o processo inverso, executando a lógica da migração e a removendo da `collection` .

O código destes exemplos pode ser acessado neste repositório:

https://github.com/eminetto/clean-architecture-go

Com esta solução conseguimos melhorar nossa produtividade pois temos mais experiência em Go do que em _node.js_. Além disto, podemos reaproveitar código criado no restante do projeto, como os _Use Cases_ da _Clean Architecture_. Podemos inclusive criar testes unitários para as migrações, o que deve ser um próximo passo na nossa implementação.

Se você usa a dupla Go + _MongoDB_ acredito que esta solução pode ser útil e espero ter ajudado.
