---
categories:
- apple
- docs
- performance
- php
comments: true
date: 2011-01-27T13:33:44Z
slug: processando-arquivos-de-profiling-do-xdebug-no-macosx
title: Processando arquivos de profiling do Xdebug no MacOSX
url: /2011/01/27/processando-arquivos-de-profiling-do-xdebug-no-macosx/
wordpress_id: 625
---

O Xdebug é uma das ferramentas mais úteis que conheço para quem trabalha com PHP. Eu escrevi um resumo das suas funcionalidades em um [post anterior](/blog/2007/06/05/xdebug/). 
Uma das funcionalidades que mais uso é a geração de "profiling" de aplicações. Ajuda muito na hora de encontrar "gargalos" de performance. O único problema é que eu precisava usar o [Kcachegrind](http://kcachegrind.sourceforge.net/html/Home.html) ou o [Webgrind](http://code.google.com/p/webgrind/) para analisar os arquivos gerados pelo Xdebug.
Como eu uso MacOSX eu procurei uma forma mais rápida de processar essas informações, sem ter que acessar uma máquina virtual Linux ou configurar o Webgrind. 
Para isso eu usei a dupla [xdebugtoolkit](http://code.google.com/p/xdebugtoolkit/) e [graphviz](http://www.pixelglow.com/graphviz/download/). O primeiro analisa o arquivo gerado pelo Xdebug e gera um arquivo .dot, que eu posso abrir com o graphviz.
Para instalar o xdebugtoolkit é preciso acessar o Terminal e executar os comandos:

`
svn co http://xdebugtoolkit.googlecode.com/svn/tags/0.1.5/xdebugtoolkit/ xdebugtoolkit
`
e
`
cd xdebugtoolkit/
`
Ele é um programa desenvolvido em Python, que vem instalado nativamente no MacOSX.

Com o xdebugtoolkit é possível converter o arquivo de profiling em uma imagem no formato .dot. Para isso é preciso executar o comando:
`
./cg2dot.py cachegrind.out.5398 > cachegrind.dot
`
Agora basta usar o graphviz para abrir o arquivo cachegrind.dot. A instalação segue o formato .DMG do MacOSX e não apresenta nenhum mistério.
Abaixo um exemplo de arquivo gerado por esse processo, e exportado para JPG.
[![](/images/posts/cachegrind_150.jpg)](/images/posts/cachegrind.jpg)
No arquivo é possível ver toda a árvore de execução da página, com seus respectivos tempos de processamento, ajudando a encontrar partes que estejam comprometendo a performance. 
Com certeza não é tão avançado quanto o Kcachegrind, mas tem me ajudado bastante. 



