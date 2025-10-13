+++
bigimg = ""
date = "2017-06-29T08:00:24+02:00"
subtitle = ""
title = "Definindo APIs com o API Blueprint"

+++

Uma das melhores decisões que tomamos na Coderockr foi adotarmos a abordagem "API First" para todos os projetos que iniciamos, desde 2010. Mas nos últimos meses percebemos uma necessidade: melhorar o processo de definição e documentação das APIs. 

<!--more-->

Já usávamos [outras abordagens](http://eltonminetto.net/2016/06/01/gerando-documentacao-de-apis/), mas a maioria delas envolvia documentar a API no próprio código, usando *annotations*. Esta abordagem funciona, mas tem alguns problemas, principalmente quando a documentação precisa ser alterada por alguém de negócios. E gerar "mocks" e testes destas anotações também é um desafio complexo. 

Com isso em mente fizemos algumas pesquisas e chegamos a duas alternativas: Swagger e API Blueprint. Ambos são padrões de documentação de APIs e tem suas vantagens e desvantagens:

- Swagger: é o [padrão de mercado](https://www.openapis.org/) e vem sendo adotado por várias empresas como a Amazon. Para descrever a API é necessário criar arquivos JSON, o que facilita bastante para os programadores, mas é um pouco complexo para visualizar e alterar seu conteúdo por pessoas não tão envolvidas com código. Existe uma série de ferramentas que podem ajudar neste processo, mas isso tornou-se uma pequena barreira para nós. (bom, pelo menos não é YML... Já comentei que odeio YML?)
- API Blueprint: é uma [especificação](https://apiblueprint.org/) mais recente e foi criada por uma empresa chamada [apiary](https://apiary.io/), comprada pela Oracle. A grande vantagem do API Blueprint é ser descrita em Markdown, o que facilita bastante a edição dos documentos, mesmo por quem não tem familiaridade com código. Além disso, existe uma série de ferramentas disponíveis que permitem gerar documentos no padrão Swagger, "mock servers" e testes. 

Optamos pelo API Blueprint pela facilidade de uso e agilidade que isso nos trouxe. Vou demonstrar com um pequeno exemplo. 

# Definindo uma API

A definição é escrita em um arquivo no formato Markdown, que pode ser nomeado como "api.md" ou "api.apib". Ambos funcionam, mas se usarmos a extensão .apib podemos aproveitar plugins para editores como o SublimeText que auxiliam na escrita. Os plugins podem ser encontrados no site oficial da especificação. 

Nosso exemplo:

```md

FORMAT: 1A
HOST: http://api.sample.com.br/v1

# Sample da API

Descrição da Sample.

# Group API

## Sobre [/]

Aqui podemos descrever detalhes que são comuns a todos os serviços como formatos, headers, tipos de erros, etc

# Group Mensagem

## Mensagens [/message]

### Criar mensagens [POST]

+ Request Criar uma mensagem

    + Headers

            Accept: application/json
            Content-Type: application/json
    + Attributes (Message)

+ Response 201 (application/json)
    + Attributes (Created)

### Listar mensagens [GET]

+ Response 200 (application/json)
    + Attributes (array[Message])

+ Response 404 (application/json)
    + Attributes (Error)


## Mensagem [/message/{id_message}]

+ Parameters
    + id_message: 1 (number, required) - ID da mensagem

### Ver mensagem [GET]

+ Response 200 (application/json)
    + Attributes (Message)

+ Response 404 (application/json)
    + Attributes (Error)


### Excluir mensagem [DELETE]

+ Response 200 (application/json)
    + Attributes (Id)

+ Response 404 (application/json)
    + Attributes (Error)


### Alterar mensagem [POST]

+ Request Alterar uma mensagem

    + Headers

            Accept: application/json
            Content-Type: application/json
    + Attributes (Message)

+ Response 200 (application/json)
    + Attributes (Created)

+ Response 400 (application/json)
    + Attributes (Error)


## Anexos [/message/{id_message}/file]

+ Parameters
    + id_message: 1 (number) - ID da mensagem

### Listar anexos [GET]

+ Response 200 (application/json)
    + Attributes (array[File])

+ Response 404 (application/json)
    + Attributes (Error)

## Anexos [/message/{id_message}/file/{file_id}]

+ Parameters
    + id_message: 1 (number) - ID da mensagem
    + file_id: 1 (number) - ID do arquivo

### Ver anexo [GET]

+ Response 200 (application/json)
    + Attributes (File)

+ Response 404 (application/json)
    + Attributes (Error)

### Remover anexo [DELETE]

+ Response 200 (application/json)
    + Attributes (Id)

+ Response 400 (application/json)
    + Attributes (Error)

# Data Structures

## Message (object)
+ subject (string) - Assunto da mensagem
+ body (string) - Corpo da mensagem


## MessageUpdate (Message)
+ id_message (number) - Id da mensagem

## File (object)
+ id_file (number) - Id do arquivo
+ name (string) - Nome do arquivo
+ url (string) - Url do arquivo

## Created (object)
+ id (number) - Id gerado

## Id (object)
+ id (number) - Id a ser processado

## Error (object)
+ code: 400 (number) - Status code
+ message (string) - Mensagem do status
+ description (string) - Descrição do status


## Multi (object)
+ id (number) - Código da entidade
+ code: 200 (number) - Status code
+ message (string) - Descrição do status


## User (object)
+ id (number) - Código do usuário
+ name (string) - Nome do usuário
+ token (string) - Token do usuário conectado

```

No site da especificação é possível ver os detalhes, mas basicamente o que fazemos é definir as URLs, o formato das requisições e das respostas. Podemos definir estruturas de dados simples e complexas e usá-las para descrever o que a API espera de entradas e o que deve gerar de saída. O documento é relativamente simples de entender e alterar, o que foi um dos pontos de maior peso para nossa escolha. Mesmo assim, podemos melhorar a apresentação. 

## Gerando documentação

Dentre as ferramentas disponíveis no site oficial a [aglio](https://github.com/danielgtaylor/aglio) é uma das mais interessantes para geração de uma apresentação HTML da nossa definição. Ele pode ser instalado via:

	npm install -g aglio

Para gerar a documentação podemos usar o comando:

	aglio -i api.apib --theme-full-width --no-theme-condense -o index.html

No site da ferramenta é possível ver todas as opções de customização de temas e apresentação. Outro comando útil é o:

	aglio -i api.apib --theme-full-width --no-theme-condense -s

Ele gera um servidor local, na porta 3000, que fica observando alterações no arquivo .apib e atualiza automaticamente a página da documentação. Isso facilita bastante a manutenção do documento. 
Um exemplo da documentação gerada:

[![aglio](/images/posts/aglio.png)](/images/posts/aglio.png) 

A documentação ajuda muito no processo de desenvolvimento dos clientes da API, mas podemos ir além.

## Gerando um mock server

Com a API definida as equipes de frontend (web, mobile, etc) e backend (quem vai desenvolver a API) podem trabalhar em paralelo. Para facilitar ainda mais podemos criar um "mock server" que vai gerar dados falsos baseados na definição da API. Assim a equipe de frontend pode trabalhar sem precisar esperar a equipe de backend terminar a implementação. Para isso vamos usar outra ferramenta, a [drakov](https://github.com/Aconex/drakov).

Para instalar a ferramenta basta executar:

	npm install -g drakov

E para gerar o servidor:

	drakov -f api.apib -p 4000

Desta forma temos uma API funcional que pode ser usada para testes e desenvolvimento. 

O passo final é definirmos uma forma de validarmos nossa API. 

## Testando

Podemos usar uma ferramenta chamada [apib2swagger](https://github.com/kminami/apib2swagger) para gerar um arquivo Swagger da nossa API e realizarmos testes usando algum recurso do Swagger. Optamos por usar o [dredd](https://github.com/apiaryio/dredd) que automatiza os testes, tanto usando API Blueprint quanto Swagger. 

Para instalá-lo:

	npm install -g dredd

E para executar os testes:

	dredd api.apib http://localhost:4000

Neste exemplo estou usando o dredd para testar nosso "mock server", por isso o resultado deve ser positivo. Podemos colocar o dredd na execução do nosso servidor de integração contínua para garantir que a implementação da API sempre esteja de acordo com a documentação, evitando surpresas e documentos abandonados. 

Com o conjunto API Blueprint + aglio + drakov + dredd conseguimos mapear todo o ciclo de vida de uma API: definição, documentação, desenvolvimento e testes. Os resultados estão sendo bem positivos e devemos adotar essa solução em todos os novos projetos. 