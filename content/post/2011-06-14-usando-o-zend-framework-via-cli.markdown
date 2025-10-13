---
categories:
- php
- Zend Framework
comments: true
date: 2011-06-14T14:18:40Z
slug: usando-o-zend-framework-via-cli
tags:
- cli
- php
- zend framework
title: Usando o Zend Framework via CLI
url: /2011/06/14/usando-o-zend-framework-via-cli/
wordpress_id: 739
---

Existem várias tarefas dentro de um sistema web que podem/devem ser executadas direto no servidor, sem a interação com o usuário, como workers do Gearman, tarefas agendadas na crontab do Linux, etc.
Estas tarefas podem ser executadas com o PHP-CLI (Command Line Interface). Na [documentação oficial](http://br.php.net/manual/en/features.commandline.php) do PHP existem exemplos bem fáceis de entender e extender. Nesse post vou comentar como usar o CLI em projetos com o Zend Framework.
O primeiro passo é criar um Bootstrap simplificado, que vou chamar de clip.php. Ele tem a mesma tarefa do Bootstrap normal de uma aplicação Zend Framework, com algumas coisas a menos como o uso das variáveis $_GET e $_POST. Eu criei dois exemplos de cli.php, um para [projetos usando módulos](https://github.com/eminetto/Template-ZF-Modulos) e outro para [projetos mais simples](https://github.com/eminetto/Template-ZF). 
Para usá-los basta usar o projeto exemplo que está no Github ou baixar apenas o cli.php e customizá-lo para seu projeto. Para executar é:

`php cli.php -e development -a default/index/index`

Você precisa ter o executável do PHP em seu Path. No Windows você iria executar:

`php.exe cli.php -e development -a default/index/index`

Os parâmetros significam:
-e = ambiente que você está executando. Se não passar parâmetro o script vai considerar como "development". Isso é usado para ler as configurações de caminhos e dados do config.ini
-a = o que você irá executar. O formato é modulo/controlador/acao/param/valor

Se precisar passar parâmetros para a ação é só adicioná-los no comando, da mesma forma como faria em um navegador. Exemplo:

`php cli.php -e development -a default/index/index/id/10/nome/elton`

Desta forma a action indexAction do controlador IndexController vai poder acessar os parâmetros id e nome usando o comando:

`
$id = $this->getRequest()->getParam('id');
$nome = $this->getRequest()->getParam('nome');
`

No caso de não estar usando módulos no seu projeto basta invocar sem o nome do módulo:

`php cli.php -e development -a index/index/id/10/nome/elton`

Nós usamos bastante esse recurso em diversas tarefas agendadas nos projetos da [Coderockr](http://www.coderockr.com). É algo realmente útil.
