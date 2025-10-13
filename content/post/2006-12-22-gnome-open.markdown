---
categories:
- home
- ubuntu
comments: true
date: 2006-12-22T13:34:50Z
slug: gnome-open
title: gnome-open
url: /2006/12/22/gnome-open/
wordpress_id: 180
---

Lendo este [blog ](http://ubuntu.wordpress.com/2006/12/16/gnome-open-open-anything-from-the-command-line/)encontrei uma documentação sobre o comando gnome-open. A funcionalidade é a mesma do open do OSX. Exemplos:

gnome-open www.eltonminetto.net - vai abrir o site no navegador padrão do gnome. Caso seja o Firefox e ele estiver aberto será criada uma nova aba

gnome-open mailto:email@host.com - abre uma nova mensagem no aplicativo de e-mails. O padrão é abrir no Evolution

gnome-open . - abre o diretório atual no Nautilus

gnome-open /home/elm - abre o diretório no Nautilus

gnome-open arquivo.pdf - será aberto no Evince

gnome-open arquivo.txt - será aberto no Gedit

Isto pode ser feito com qualquer arquivo e ele respeita as configurações do gnome na escolha do aplicativo a ser usado.

Para não ter que digitar gnome-open toda vez pode ser criado um alias para diminuir o comando.
Para fazer isso adicionei a seguinte linha no meu .bashrc

alias g='gnome-open'

assim eu só preciso digitar "g ." para abrir o diretório atual no Nautilus
