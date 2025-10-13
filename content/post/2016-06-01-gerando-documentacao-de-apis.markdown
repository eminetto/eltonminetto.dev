---
categories: null
comments: true
date: 2016-06-01T00:00:00Z
title: Gerando documentação de APIs
url: /2016/06/01/gerando-documentacao-de-apis/
---

Uma das melhores decisões técnicas que tomei na minha carreira foi investir pesado nas arquiteturas baseadas em serviços. Meu [primeiro post sobre isso](http://eltonminetto.net/blog/2012/03/13/soa-e-silex/) data de 2011 e desde então esta decisão só se provou um acerto. 

Uma das tarefas mais importantes, e chatas, é manter a documentação das APIs sempre atualizadas pois elas são consumidas por cada vez mais camadas: frontend, mobile, outros serviços e sistemas. 

<!--more-->

Existem várias ferramentas para esta tarefa, sendo uma das mais completas, e complexas, o [Swagger](http://swagger.io), além de alguns serviços pagos. Estamos usando o Swagger em um [grande projeto](http://compufacil.com.br), mas eu estava a procura de algo mais simples para ser usado no [Planrockr](http://planrockr.com) e encontrei o [APIDOC](http://apidocjs.com). 

Trata-se de um aplicativo escrito para o NodeJS que lê anotações nos seus códigos e gera uma documentação em HTML bem simples mas eficaz. Além das linguagens que usam o formato _Javadoc Style_ ( C#, Go, Dart, Java, JavaScript, PHP, TypeScript) ele consegue gerar documentação em arquivos CoffeeScript, Elixir, Erlang, Perl, Python e Ruby. 

Para instalar a ferramenta é necessário ter instalado o gerenciador de pacotes do NodeJS, o _npm_ e rodar o comando:

	npm install apidoc -g 

No site oficial é possível ver todas as anotações disponíveis, mas abaixo um exemplo de uma documentação do Planrockr:

```php

/**
 * @api {post} /bitbucket/saveRepository Save a new repository
 * @apiVersion 1.0.0
 * @apiName SaveRepository
 * @apiGroup Bitbucket
 *
 * @apiParam (parameters) {String} projectId Project's ID
 * @apiParam (parameters) {String} repository[uuid] Repository's Id
 * @apiParam (parameters) {String} repository[owner] Repository's Owner
 * @apiParam (parameters) {String} repository[slug] Repository's Slug
 *
 * @apiError (406) InvalidParameters Invalid parameters
 * @apiError (404) ProjectNotFound Project not found
 * @apiError (406) RepositoryAlreadyUsed Repository already used
 *
 * @apiSuccess (201) {String} status Status
 * @apiSuccess (201) {String} data  Ok
 * @apiSuccess (201) {Number} statusCode  Status Code
 */

```

O próximo passo é criar na raiz do seu projeto um arquivo chamado _apidoc.json_ com algumas configurações básicas:

```javascript

{
  "name": "Planrockr",
  "version": "1.0.0",
  "description": "Planrockr",
  "title": "Planrockr API",
  "url" : "http://app.planrockr.dev/rpc/v1"
}

```

E para gerar a documentação basta executar:


	apidoc -c apidoc.json -i . -e backend/vendor -e planrockr-backend/frontend/bower_components  -o ./apidoc

Para ver as opções do comando é só usar o 

	apidoc --help

Alguns exemplos da documentação gerada:

[![apidoc1](/images/posts/apidoc1.png)](/images/posts/apidoc1.png) 

[![apidoc2](/images/posts/apidoc2.png)](/images/posts/apidoc2.png) 


Além de ser bem bonita e fácil de acessar é possível incluir a sua geração em um script de commit, build ou deploy. E por suportar várias linguagens é fácil manter uma documentação unificada e gerada automaticamente. 

O que você vem usando para esta tarefa? Se tiver outra sugestão por favor complemente aqui nos comentários. 

