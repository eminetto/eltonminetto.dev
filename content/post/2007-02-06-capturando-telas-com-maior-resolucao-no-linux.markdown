---
categories:
- home
- ubuntu
comments: true
date: 2007-02-06T21:49:52Z
slug: capturando-telas-com-maior-resolucao-no-linux
title: None
url: /2007/02/06/capturando-telas-com-maior-resolucao-no-linux/
wordpress_id: 188
---

Essa semana precisei capturar algumas telas para um livro que estou escrevendo. Normalmente usaria a ferramenta padrão do Gnome ou do KDE, usando a tecla PrtSc. Mas o problema é que usando-se essas  ferramentas a resolução do arquivo é de 72 dpi, a resolução do monitor, mas para imprimir a gráfica pede uma resolução maior, algo em torno de 300 dpi. Para fazer isso usei o comando import do pacote ImageMagic.
Para instalar no Ubuntu é só:

sudo apt-get install imagemagick

Para utilizar o import para capturar a tela:

import -border -frame -density 300 nome_imagem.jpg

Assim que executar este comando o cursor vira um alvo e é só clicar na tela desejada que o arquivo nome_imagem.jpg é criado, pronto para mandar para a gráfica. Os parâmetros -border e -frame indicam para o import capturar também a borda e a decoração da janela. O -density indica a resolução desejada.
