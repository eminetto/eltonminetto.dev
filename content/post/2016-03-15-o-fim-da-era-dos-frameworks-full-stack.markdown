---
categories: null
comments: true
date: 2016-03-15T00:00:00Z
title: O fim da era dos frameworks full stack
url: /2016/03/15/o-fim-da-era-dos-frameworks-full-stack/
---

Venho acompanhando de perto a evolução dos frameworks PHP desde meados de 2004 e me parece que todos estão caminhando para uma abordagem cada vez mais focada ao conceito de micro-frameworks. 
<!--more-->
Um pouco de história... Nos primórdios havia o caos, e então veio o Ruby on Rails... E as outras linguagens viram que era legal e criaram suas versões do "framework MVC perfeito", como o Zend Framework, Symfony, CakePHP, Code Igniter, etc. Mas todos eles eram muito monolíticos, com sua grande quantia de componentes fortemente acoplados e com isso o desenvolvimento de grandes projetos, com manutenções e expansões constantes tornou-se cada vez mais complexa. Percebendo isso grandes figuras do mundo PHP se uniram e criaram um grupo chamado PHP-FIG que criou os padrões PSR, com isso permitindo o surgimento do Composer e outras inovações. 

A próxima evolução dos frameworks corrigiu um grande número de problemas das suas versões anteriores e com nomes como Zend Framework 2, Symfony 2 vimos uma nova era de desenvolvimento surgir. Projetos menos acoplados, componentes mais facilmente intercambiáveis, desenvolvedores mais produtivos e felizes. 

Mas usar um framework full stack como o ZF2 ainda era grande demais para a maioria das aplicações que desenvolvemos no dia a dia, especialmente quando começamos a usar arquiteturas baseadas em serviços e APIs. Isso acarretou a criação de micro-frameworks como o Slim e o Silex. Com os micro-frameworks agora podemos iniciar um projeto de forma rápida e simples e ir adicionando componentes na proporção que eles são necessários. É muito mais fácil iniciar com algo pequeno e adicionar recursos do que começar um projeto com algo enorme e remover componentes desnecessários e que podem causar perda de performance. 

A próxima grande evolução que nos levou a esse caminho foi a aprovação do PSR7, que padroniza a forma como os frameworks e componentes manipulam Requests e Responses, o coração de todo e qualquer aplicativo web. Ao invés de usarmos o padrão MVC, que foi portado do mundo desktop para a web, agora temos um padrão que nasceu para este ambiente dinâmico e específico. Com a adoção deste padrão estamos vendo os grandes frameworks como o Zend Framework, Symfony e Laravel se tornando micro-frameworks (ZF3, Symfony 3, Lumen) e projetos que já nasceram com essa abordagem, como o Slim, evoluindo a passos largos. 

Então me parece seguro dizer que o futuro pertence aos micro-frameworks, micro-serviços, containers e cloud computing. É hora de pensarmos em nossos projetos desta forma e usufruirmos de toda a tecnologia que está surgindo ao nosso redor. 

O que você acha? Estamos vendo o fim dos frameworks full stack? 