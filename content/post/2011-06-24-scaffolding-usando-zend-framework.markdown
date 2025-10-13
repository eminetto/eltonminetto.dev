---
categories:
- etc
comments: true
date: 2011-06-24T16:58:18Z
slug: scaffolding-usando-zend-framework
title: Scaffolding usando Zend Framework
url: /2011/06/24/scaffolding-usando-zend-framework/
wordpress_id: 754
---

Segundo a Wikipedia, scaffolding é: 


> 
...termo é usado em programação para indicar que o código a que se refere é apenas um esqueleto usado para tornar a aplicação funcional, e se espera que seja substituído por algoritmos mais complexos à medida que o desenvolvimento da aplicação progride.



A idéia do scaffolding é gerar código de forma rápida, geralmente para você poder testar algum conceito ou modelagem de banco de dados, mas nada impede de usar o código gerado para algumas aplicações pequenas. 
Essa é uma função que já vi em outros frameworks, como CakePHP, Django e Rails (por favor me corrijam se eu estiver errado). No Zend Framework isso ainda não existe, mas um programador criou um projeto para podermos usar os componentes padrão do framework (Zend_Form, Zend_Validate) e gerar códigos em tempo de execução para as tarefas básicas de manipulação de dados de uma tabela, o famoso CRUD (Create, Retrieve, Update,Delete)
Para usar precisamos fazer o download do código no site abaixo:

[http://code.google.com/p/zendscaffolding/](http://code.google.com/p/zendscaffolding/)

Após baixar o arquivo e descompatá-lo você vai encontrar um diretório com o conteúdo:
`
README.txt - exemplos de como usar o componente
Scaffolding.php - a classe, que deve ser salva no diretório library/Zend/Controller/
tests - uma aplicação de exemplo 
views - diretório com as visões usadas pelo componente. Deve ser salvo no diretório views/scripts do seu projeto
`
Vamos fazer um exemplo de uso. 

Tendo uma tabela no banco de dados com a seguinte estrutura:

    
    
    CREATE TABLE `users` (
      `id` tinyint(4) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(45) NOT NULL,
      `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
      `role` enum('admin','redator','revisor') DEFAULT 'revisor',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;
    


Após seguirmos os passos normais de uma aplicação Zend Framework, criando o model para tratar esta tabela podemos criar um controlador:

[UserController.php](https://gist.github.com/1045552)

Somente isto é necessário. Não é preciso criar as visões ou outros métodos. Ao acessar a URL
http://projeto/users

É apresentado :

[![](/images/posts/captura-de-tela-2011-06-24-c3a0s-16-41-25_150.png)](/images/posts/captura-de-tela-2011-06-24-c3a0s-16-41-25.png)

Esta tela é gerada usando-se as visões do diretório scaffolding dentro do views/scripts, então podemos alterá-las para termos uma interface mais amigável.

Ao clicar em adicionar ou editar um usuários somos apresentados o formulário:
[![](/images/posts/captura-de-tela-2011-06-24-c3a0s-16-43-23_150.png)](/images/posts/captura-de-tela-2011-06-24-c3a0s-16-43-23.png)

É interessante notar que os campos ENUM da base de dados foram apresentados como campos SELECT, o que facilita o preenchimento.
Um dos detalhes que achei interessante no código é que ele usa todos os componentes padrão do Zend Framework, então é muito fácil extender a classe Zend_Controller_Scaffolding e usar validators, decorators e outras funcionalidades.
No README.txt que vem incluso no pacote, e na documentação do site existem outros exemplos mais complexos, inclusive com o uso de chaves estrangeiras e tabelas relacionais, tudo muito simples de usar.
O desenvolvedor apresentou [uma proposta de inclusão](http://framework.zend.com/wiki/display/ZFPROP/Zend_Controller_Scaffolding+-+Alex+Oroshchuk) desta classe na versão oficial do framework, mas o processo ainda está em andamento, mas espero que seja aprovado, pois tenho usado bastante e acho uma feature realmente útil
