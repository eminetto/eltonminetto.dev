---
categories:
- etc
comments: true
date: 2012-12-18T16:24:11Z
slug: plugin-para-o-mysql-workbench-gerar-sqlite
title: Plugin para o MySQL Workbench gerar SQLite
url: /2012/12/18/plugin-para-o-mysql-workbench-gerar-sqlite/
wordpress_id: 1224
---

O [MySQL Workbench](http://www.mysql.com/products/workbench/) é uma ferramenta de grande utilidade na fase de planejamento e modelagem de sistemas que venho usando há alguns anos. Recentemente adicionei o [Astah](/blog/2012/04/19/uml-usando-astah/) mas o Workbench continua constando na minha caixa de ferramentas.

Como o próprio nome diz ele é voltado para MySQL mas graças a sua arquitetura baseada em plugins podemos expandir suas funcionalidades para outros bancos de dados. Um desses plugins é o "[SQLite export](http://www.henlich.de/software/sqlite-export-plugin-for-mysql-workbench/)". 
<!--more-->
Para instalá-lo basta fazer o [download](http://www.henlich.de/media/ExportSQLite.grt.lua) do arquivo no site, abrir o Workbench, escolher a opção Scripting->Install Plugin/Module e selecionar o arquivo. 
Depois de reiniciar o Workbench um novo item aparece no menu Plugins->Utilities->Export SQLite CREATE script. Clicando nessa opção é solicitado o caminho e o nome do arquivo a ser salvo com o script de criação da modelagem aberta atualmente.

Testei o plugin com algumas modelagens bem complexas e o resultado foi perfeito. Estou usando este plugin para criar versões SQLite de bancos de dados MySQL, para usar em testes unitários, e também para facilitar o trabalho dos desenvolvedores da Coderockr que usam o SQLite nos aplicativos iOS e Android.

Não pesquisei plugins para outros bancos de dados, mas acredito que seja fácil de encontrá-los e dar mais poder a essa ótima ferramenta.
