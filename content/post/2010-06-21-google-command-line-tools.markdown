---
categories:
- home
comments: true
date: 2010-06-21T14:03:34Z
slug: google-command-line-tools
title: Google Command Line Tools
url: /2010/06/21/google-command-line-tools/
wordpress_id: 425
---

O pessoal do Google lançou mais uma contribuição para o mundo Open Source. Trata-se do Google Command Line Tools. Desenvolvido em Python facilita a criação de scripts para usar algumas das ferramentas da empresa como o Blogger, Picasa, Youtube, Google Calendar e Google Docs.
Para instalar no Mac OSX é preciso primeiro instalar o Python-Gdata:
- Download no [http://code.google.com/p/gdata-python-client/](http://code.google.com/p/gdata-python-client/)
- Após descompactar e entrar no diretório basta digitar (no terminal, lógico): sudo python setup.py install
Para instalar o Googlecl:
- Download no [http://code.google.com/p/googlecl/](http://code.google.com/p/googlecl/)
- Após descompactar e entrar no diretório basta digitar: sudo python setup.py install
Alguns exemplos: (do site oficial)
Blogger
  $ google blogger post --title "Título" "Texto do post"
Calendar
  $ google calendar add "Beber com o pessoal at noon tomorrow"
Contacts
  $ google contacts list name,email > contacts.csv
Picasa (útil para fazer upload de várias imagens de uma só vez)
  $ google picasa create --title "Fotos da festa" ~/photos/festa/*.jpg
Youtube
   $ google youtube post --category Education killer_robots.avi

E o meu favorito: Editar um documento do Google Docs usando o VIM!!!
google docs edit --title "Lista de convidados para a festa" --editor vim

A primeira vez que você usar cada um dos comandos é solicitado que aceite a conexão entre o aplicativo e a linha de comando. As próximas vezes não é mais necessária a confirmação
Eu já estou pensando em algumas utilidades em meus scripts. E você? Se tiver sugestões deixe um comentário ;)
