---
categories:
- home
- python
- ubuntu
comments: true
date: 2006-08-19T19:53:40Z
slug: gerando-syntax-highlighting-com-enscript
title: Gerando Syntax Highlighting com enscript
url: /2006/08/19/gerando-syntax-highlighting-com-enscript/
wordpress_id: 160
---

Com a ferramenta enscript é possível gerar páginas html com o conteúdo de um programa ou script com o sempre útil syntax highlighting, o que facilita bastante a leitura de um código-fonte.

Para instalar:

sudo apt-get install enscript

Para gerar uma página html com o conteúdo de um script em Python:

enscript --color --language=html -Epython --output cliente.htm cliente.py

O arquivo [cliente.htm](/codes/cliente.htm) será gerado com o conteúdo do cliente.py com as cores já formatadas para facilitar a leitura do código.

Diversas linguagens de programação estão disponíveis, como é listado no [site](http://www.codento.com/people/mtr/genscript/highlightings.html)
