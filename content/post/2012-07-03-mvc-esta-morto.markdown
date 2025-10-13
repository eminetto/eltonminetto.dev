---
categories:
- etc
comments: true
date: 2012-07-03T09:15:13Z
slug: mvc-esta-morto
title: MVC está morto?
url: /2012/07/03/mvc-esta-morto/
wordpress_id: 1074
---

Um [artigo](http://cirw.in/blog/time-to-move-on) tem feito um certo burburinho na Internet essa semana, dizendo que o MVC está morto, que é hora de seguirmos em frente e usarmos outras coisas, como o MOVE (Model, Operations, Views and Events) citado pelo autor.

[caption id="attachment_1075" align="alignnone" width="150"][![](/images/posts/move_150.jpg)](/images/posts/move.jpg) MOVE  
http://cirw.in/images/move.jpg[/caption]

Lendo o artigo e refletindo um pouco percebi que estou usando algo similar nos últimos projetos da Coderockr.
A grande parte dos nossos projetos é composta por uma interface iOS (iPhone ou iPad), Android (celulares ou tablets) e web. Para isso estamos usando o conceito de SOA (Service Oriented Architecture).

Criamos entidades, escritas usando o Doctrine, que apenas representam tabelas de um banco de dados, sem lógica embutida. Toda a lógica de manipulação dessas entidades está escrita na forma de serviços, também usando Doctrine e expostas na forma de web services usando o micro framework Silex. Usando o mesmo exemplo do artigo citado no começo, temos uma entidade chamada User e um serviço chamado AuthenticationService, que recebe um login e senha, carrega a entidade User e verifica se as informações estão corretas, retornando sempre um JSON com o resultado.
As entidades também podem ser acessadas diretamente, usando o conceito de REST, onde os comandos HTTP servem para indicar se você está consultando uma entidade (GET), adicionando (POST), alterando (PUT) ou excluindo (DELETE). O resultado dessas chamadas também é sempre um documento JSON.

Com a lógica toda desenvolvida basta desenvolvermos as interfaces iOS, Android e web para consumirem esses serviços. Para a interface web estamos usando o Zend Framework e jQuery. Com o ZF temos a facilidade de usarmos coisas como Zend_Form, Zend_Service (para consumir os serviços e entidades), Zend_Cache, etc, e com o jQuery podemos usar AJAX para também acessar os serviços e entidades. Dessa forma acabamos usando apenas os Controllers e Views do MVC, conceito no qual o ZF é fortemente baseado. Os Models deixam de existir nesse caso.

Claro que esse formato pode não ser o mais adequado para todas as ocasiões, mas no nosso caso tem sido muito importante pois fica muito fácil alterar a lógica de um aplicativo e facilmente refletir isso para todas as interfaces.
Pensando novamente no título desse post, será que o MVC está mesmo morrendo, ou sendo alterado? Me parece que sim, ainda mais se levarmos em conta coisas novas como Meteor e tudo que a galera do Javascript está nos trazendo de bom. De qualquer forma, MVC morrendo ou não, é um momento bem interessante para a área de desenvolvimento de software.

P.S.: se quiser ver os códigos da nossa solução é só verificar os dois repositórios do Github: [SOA-Server](https://github.com/Coderockr/SOA-Server) e [SOA-Client](https://github.com/Coderockr/SOA-Client).
