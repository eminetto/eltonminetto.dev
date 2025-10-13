---
categories:
- etc
comments: true
date: 2012-09-04T11:29:23Z
slug: silex-composer-e-a-empolgacao-renovada-pelo-php
title: Silex, Composer e a empolgação renovada pelo PHP
url: /2012/09/04/silex-composer-e-a-empolgacao-renovada-pelo-php/
wordpress_id: 1128
---

Lá por meados de 1999, depois de escrever alguns scripts em Perl (e odiar isso) eu descobri uma linguagem que estava despontando, um tal de PHP. Desde então venho trabalhando quase que diariamente com ela, e com essa bagagem (leia-se idade avançada) posso dizer com certeza: o último ano renovou minha empolgação com o PHP!
Vou usar esse post para citar alguns dos motivos dessa minha animação.

[**PHP 5.4**](http://php.net/manual/en/migration54.new-features.php)

Melhorias na sintaxe, traits, servidor web embutido, isso tudo adiciona novas facilidades e coisas úteis para os programadores no dia a dia.

**Micro Frameworks**

Sou um grande fã e defensor de frameworks, tendo usado vários deles no decorrer dos anos. Os micro frameworks surgiram para suprir a necessidade de termos algo simples para tarefas simples como criar um pequeno site, criar uma prova de conceito ou uma API. Dentre as opções existentes a que me agradou mais foi o [Silex](http://silex.sensiolabs.org), que venho usando a alguns meses e gostando muito, principalmente por ser baseado em componentes que podemos adicionar conforme a necessidade.

**[Composer](http://getcomposer.org)**

O Composer é uma das melhores criações de ultimamente. Com ele fica muito simples instalar e gerenciar dependências dentro de um projeto.

**[PHP The Right Way](http://www.phptherightway.com)**

Trata-se de um site colaborativo onde está sendo reunido a melhor e mais moderna documentação sobre o PHP. Qualquer pessoa pode colaborar fazendo um fork do projeto no Github e adicionando um pull request com a documentação atualizada.
Atualmente os documentos são em inglês e isso pode ser uma boa oportunidade para quem quer contribuir para a comunidade PHP traduzindo para o português.

Para demonstrar as facilidades do Silex e do Composer eu criei um [projeto](https://github.com/eminetto/silex-sample) no Github para usar nos meus projetos, e que pode ser útil para mais pessoas. É um "esqueleto" de projeto usando Silex, Doctrine e o Composer para gerenciar as dependências. A idéia é usar ele para gerar rapidamente uma estrutura para um novo projeto, bastando configurar a base de dados, criar as entidades do Doctrine e acrescentar a lógica necessária.
O Composer entrou para facilitar o gerenciamento das dependências do projeto (Doctrine, componentes do Symfony) e para uma funcionalidade que eu não conhecia, o "create-project" que me foi apresentado no grupo de [PHP no Facebook](http://www.facebook.com/groups/14811750159/) (um dos únicos motivos por eu não ter removido a minha conta do Facebook ainda).
Para criar um novo projeto baseado no silex-sample basta executar os comandos:

`
curl -s https://getcomposer.org/installer | php
php composer.phar create-project eminetto/silex-sample project_name
`

O primeiro comando instala o Composer na sua máquina. O segundo cria um novo diretório chamado "project_name" e usa o Composer para baixar todas as dependências. Simples assim!
Para isso funcionar eu criei o arquivo composer.json com as dependências do projeto e cadastrei no [http://packagist.org](http://packagist.org) o repositório do Github como um [pacote](http://packagist.org/packages/eminetto/silex-sample).

Espero que a comunidade PHP continue nesse ritmo de melhoria contínua e crescimento rápido pois isso é bom para todos os envolvidos.
