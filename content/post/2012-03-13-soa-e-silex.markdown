---
categories:
- etc
comments: true
date: 2012-03-13T10:29:11Z
slug: soa-e-silex
tags:
- doctrine
- jquery
- php
- rest
- rpc
- silex
- soa
- symfony
title: SOA e Silex
url: /2012/03/13/soa-e-silex/
wordpress_id: 921
---

Nos últimos anos a arquitetura SOA ([Service-oriented architecture](http://en.wikipedia.org/wiki/Service-oriented_architecture?utm_source=twitterfeed&utm_medium=twitter)) deixou de ser uma tendência e virou realidade em diversos projetos e empresas. A maioria dos projetos que a [Coderockr](http://www.coderockr.com) desenvolveu em 2011, e tem planejada para 2012, foi desenvolvida dessa forma.

Um dos exemplos é o [uFun](http://ufun.coderockr.com/). Basicamente, a arquitetura da aplicação é:

[![Imagem](/images/posts/captura-de-tela-2012-03-13-c3a0s-09-57-10_426.png)](/images/posts/captura-de-tela-2012-03-13-c3a0s-09-57-10.png)

 

Essa é a típica aplicação que se beneficia desse tipo de arquitetura, pois temos várias interfaces acessando os mesmos dados e lógica. Os dados trafegam na forma de JSON via protocolo HTTP. Muito fácil de trabalhar em todas as plataformas e linguagens usadas no projeto (Objective C, Java, PHP e JavaScript).

Outro assunto que me interessa muito é o de frameworks. Ultimamente tenho estudado bastante os novos micro-frameworks de PHP. A parte server do uFun foi desenvolvida pelo [@xorna](http://twitter.com/xorna) em [Slim Framework](http://www.slimframework.com/), que comentei em outro [post](/blog/2011/11/29/slim-framework/).  Além do Slim Framework outro framework que me pareceu interessante foi o [Silex](http://silex.sensiolabs.org/), principalmente por ser baseado em componentes do Symfony. 

Como eu tenho uma teoria de que só aprendemos alguma tecnologia quando precisamos desenvolver algo com ela eu me fiz um desafio: criar um aplicativo em Silex para facilitar a criação de serviços Rest e RPC. O resultado está no Github da Coderockr: [https://github.com/Coderockr/SOA-Server](https://github.com/Coderockr/SOA-Server)

Além do Silex eu usei alguns componentes que eu achei importantes:

  * [DMS/Filter](https://github.com/rdohms/DMS-Filter) do meu amigo [Rafael Dohms](http://twitter.com/rdohms) para fazer o filtro dos dados usando annotations
  * [Doctrine](http://www.doctrine-project.org/) para fazer o ORM das entidades
  * [Symfony ClassLoader](https://github.com/symfony/ClassLoader)  para facilitar o carregamento dos outros componentes
  * [Symfony Validator](https://github.com/symfony/Validator.git) para fazer a validação das entidades

No [README](https://github.com/Coderockr/SOA-Server/blob/master/README.md) do projeto tem mais informações sobre como fazer o download e instalação. Quanto ao funcionamento:

Toda a lógica do projeto está no arquivo i[ndex.php](https://github.com/Coderockr/SOA-Server/blob/master/index.php). Essa é uma das vantagens do Silex, por ser um micro-framework. Tudo é muito simples, principalmente a criação de rotas, validações. O index.php faz o papel de bootstrap da aplicação e possui as rotas para os métodos Rest (GET, PUT, POST e DELETE) e RPC.

Para que uma entidade esteja disponível via Rest basta que seja criada uma classe no namespace model que extends model\Entity, como no [exemplo](//github.com/Coderockr/SOA-Server/blob/master/model/User.php). Ela é uma entidade Doctrine, então temos todas as facilidades desse excelente framework. Além disso podemos adicionar as anotações para o DMS/Filter (por exemplo o @Filter\StripTags) e as configurações para o Symfony Validator (método loadValidatorMetadata)

Para que uma classe seja acessível via RPC ela precisa ser criada no namespace procedure e estender procedure\Procedure. Como Procedure é uma classe abstrata a nova classe precisa definir o método execute e retornar sempre um array com o resultado, conforme o [exemplo](https://github.com/Coderockr/SOA-Server/blob/master/procedure/Login.php).

Para termos um pouco de segurança foi implementado um controle de acesso, usando-se um header HTTP, o Authorization, que é validado baseado no arquivo configs/clients.php.

Também escrevi um [exemplo](https://github.com/Coderockr/SOA-Server/blob/master/sample.html) de como seria fácil acessar via jQuery os serviços. O mesmo pode ser facilmente feito via PHP e outras linguagens usando Curl e outras bibliotecas.

**Conclusões**

Me diverti bastante escrevendo e testando esses componentes. Além da diversão, acabei criando algo que pretendo usar nos próximos projetos da Coderockr. Então esse desafio cumpriu seus objetivos: aprendi algo novo (Silex nesse caso) e consegui criar algo útil. Se for útil para alguém mais só aumenta a minha recompensa :)

Se alguém quiser contribuir com códigos é só usar a metodologia pull-request do Github e sugerir melhorias e correções.
