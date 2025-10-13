---
categories: null
comments: true
date: 2015-11-18T00:00:00Z
title: Highlight de código para Keynote ou Powerpoint
url: /2015/11/18/highlight-de-codigo-para-keynote-slash-powerpoint/
---

Esta dica é útil para quem cria apresentações no Keynote ou no Powerpoint e precisa incluir trechos de códigos nos seus slides. A ideia é facilitar a formatação dos códigos para deixá-los mais apresentáveis. 

Estou usando no MacOS X mas a ferramenta usada, o [Highlight](http://www.andre-simon.de/doku/highlight/en/highlight.php) pode ser instalado no Linux e no Windows. 

Para instalar no Mac a maneira mais simples é usando o [Homebrew](http://mxcl.github.com/homebrew/) com o comando

```
brew install highlight
```
 
Depois de instalado é possível usá-lo para formatar os códigos, como no exemplo abaixo:

```
highlight -O rtf tmp.php --line-numbers --font-size 24 --font Hack --style edit-xcode -W -J 80 -j 3 --src-lang php | pbcopy
```

O trecho **| pbcopy"** é usado para mandar o código gerado direto para a área de transferência do Mac, desta forma basta um Cmd+V no Keynote/Powerpoint para colar o texto formatado no slide. No Linux existe um programa similar chamado [xclip](http://linux.die.net/man/1/xclip) que pode ser usado para este fim.

No trecho **--font Hack** eu escolho uma fonte a ser gerada, no meu caso a fonte Hack, que tenho instalada no Mac e pode ser substituída por outra existente no sistema operacional. 

E com o **--style** é possível escolher o tema a ser usado, com as cores para strings, comentários, etc. Com o comando abaixo é possível ver todos os temas instalados no seu SO e escolher o que melhor se adeque ao background dos slides.

```
highlight -w
```
 
Com esta pequena dica facilita bastante a criação de belas apresentações com código fonte. 