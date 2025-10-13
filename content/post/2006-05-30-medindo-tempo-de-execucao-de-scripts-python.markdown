---
categories:
- codes
- home
- python
comments: true
date: 2006-05-30T11:56:06Z
slug: medindo-tempo-de-execucao-de-scripts-python
title: None
url: /2006/05/30/medindo-tempo-de-execucao-de-scripts-python/
wordpress_id: 142
---

Numa aula eu estava explicando a técnica de programação "Divisão e Conquista", que consiste em dividir um problema grande em pequenos subproblemas e resolvê-los separadamente de forma que o resultado deles seja o resultado do problema original. Um exemplo de algoritmo desenvolvido nessa técnica é o de busca binária, onde o espaço de busca é sempre dividido pela metade, diminuindo o tempo de pesquisa. Para ilustrar a diferença desenvolvi dois scripts em Python para mostrar o tempo de execução de um algoritmo de pesquisa usando "força bruta" e a pesquisa binária. Para isso usei o módulo [timeit](http://www.python.org/doc/2.4/lib/module-timeit.html) do Python. Com este módulo é possível medir o tempo de execução de pequenos trechos de código. Abaixo os códigos usados:

[Pesquisa por Força Bruta](/codes/buscaForcaBruta.py)

[Pesquisa Binária (Divisão e Conquista)](/codes/buscaDivisaoConquista.py)

[Comparação dos tempos de execução](/codes/compara_buscas.py)

Como neste caso são poucos dados a serem pesquisados a diferença de tempo não é tão grande, mas é visível o suficiente para ilustrar aos alunos a diferença.
