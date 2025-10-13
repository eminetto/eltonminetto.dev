---
categories:
- codes
- docs
- home
- python
comments: true
date: 2006-05-12T15:26:26Z
slug: conjuntos-em-python
title: Conjuntos em Python
url: /2006/05/12/conjuntos-em-python/
wordpress_id: 136
---

Em uma das disciplinas que ministro, Algotimos e Estruturas de Dados III, consta o conteúdo de Teoria de Conjuntos aplicada em computação e Estruturas de Dados para Conjuntos. A primeira é a aplicação das teorias matemáticas de conjuntos nas linguagens de programação. O livro que utilizo [1] traz exemplos em Pascal. Pesquisando um pouco na internet encontrei exemplos em Python:

[conj.py](/codes/conj.py)

A outra matéria trata sobre conjuntos disjuntos [2]. Segundo [3] "algumas aplicações envolvem o agrupamento de n elementos distintos em uma coleção de conjuntos disjuntos. Duas operações importantes são encontrar o conjunto a que pertence um dado elemento e unir dois conjuntos." Em C é possível desenvolver estas estruturas de dados utilizando listas ou usando árvores. O código da versão em C usando listas é

[conjuntos_disjuntos.c](/codes/conjuntos_disjuntos.c)

Além disso, fiz uma pesquisa na internet para encontrar uma implementação em Python.  Encontrei a classe Grouper [4]. Um exemplo do uso da classe é:

[distset.py](/codes/distset.py)

Uma das aplicações dos conjuntos disjuntos é a determinação dos componentes conectados em um grafo não orientado.
A implementação deste algoritmo usando C e listas é:

[connected_components.c](/codes/connected_components.c)

E a implementação usando Python:

[ ](/codes/connected_components.c)[connected_components.py](/codes/connected_components.py)

[1] [http://www.temporeal.com.br/produtos.php?id=169855](http://www.temporeal.com.br/produtos.php?id=169855)
[2] [http://en.wikipedia.org/wiki/Disjoint-set_data_structure](http://en.wikipedia.org/wiki/Disjoint-set_data_structure)
[3] [http://www.livrariasaraiva.com.br/produto/produto.dll/detalhe?pro_id=466688&ID=C8F434217D5051E1012260358](http://www.livrariasaraiva.com.br/produto/produto.dll/detalhe?pro_id=466688&ID=C8F434217D5051E1012260358)
[4] [http://aspn.activestate.com/ASPN/Cookbook/Python/Recipe/387776](http://aspn.activestate.com/ASPN/Cookbook/Python/Recipe/387776)
