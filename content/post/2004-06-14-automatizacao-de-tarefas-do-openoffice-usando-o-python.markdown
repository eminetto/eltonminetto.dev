---
categories:
- codes
- python
comments: true
date: 2004-06-14T19:18:03Z
slug: automatizacao-de-tarefas-do-openoffice-usando-o-python
title: None
url: /2004/06/14/automatizacao-de-tarefas-do-openoffice-usando-o-python/
wordpress_id: 21
---

O projeto OpenOffice possui uma característica muito útil e pouco utilizada que é a capacidade de integrar seu funcionamento com outros aplicativos. Isto é possível através do UNO (Universal Network Objects), que é um modelo de componentes do OO. UNO oferece interoperabilidade entre diferentes linguagens de programação, diferentes modelos de objetos, diferentes arquiteturas e processos, em uma rede local ou mesmo através da internet. Seus componentes podem ser implementados e acessados por qualquer linguagem de programação que possua acesso aos bindings do UNO. Atualmente existem bindings para as seguintes linguagens:
* C
* C++
* Java
* Python
A utilização do UNO na linguagem python é possível usando-se o Python-UNO (pyUNO). Desde a versão OpenOffice1.1RC4 o pyUNO é incluído por padrão nas instalações do OO.org. No diretório program do OO, existe uma versão do python(2.2.2) com acesso ao pyUNO. Neste exemplo é demonstrado um script que executa o OO, abre um arquivo de texto com campos específicos, faz a mesclagem (substitui os campos por valores), imprime e fecha o arquivo.

[pyUno.py](/codes/pyUno.py)

Este exemplo ilustra algumas possibilidades. Podemos, desta forma ler dados de uma base de dados e mesclar os campos em um documento modelo, da mesma forma que é feito no Microsoft Office, mas com a vantagem de usar ferramentas livres e poder expandir a aplicação como nossa imaginação permitir.

Links

[http://goldenspud.com/blog/2004/Feb/10#build_and_export](http://goldenspud.com/blog/2004/Feb/10#build_and_export)
[http://udk.openoffice.org/python/python-bridge.html](http://udk.openoffice.org/python/python-bridge.html)
