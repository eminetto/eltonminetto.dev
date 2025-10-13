---
categories:
- docs
- home
- ubuntu
comments: true
date: 2006-07-28T20:52:09Z
slug: instalando-o-engage-no-dapper
title: Instalando o Engage no Dapper
url: /2006/07/28/instalando-o-engage-no-dapper/
wordpress_id: 154
---

**O que é o Engage**

O engage é um dock similar ao do OS X que está sendo desenvolvido em conjunto com o Enlightenment 17. A vantagem é que ele pode ser usado também em outros ambientes como o Gnome ou o XFCE.
**
Instalando**

Para instalar no Dapper é preciso adicionar no /etc/apt/sources.list :

deb http://soulmachine.net/breezy/ unstable/

Depois é preciso importar a chave pública do repositório com o comando

sudo wget soulmachine.net/public.key && sudo apt-key add public.key

depois é só atualizar e instalar:

sudo apt-get update

sudo apt-get install engage

**Configurando**

É preciso criar os diretórios onde será gravado as configurações:mkdir -p ~/.e/e/applications/allmkdir -p ~/.e/e/applications/engage

O engage quando executado irá procurar no diretório acima os módulos a apresentar. Estes módulos estão em um formato especial, o .eap, que contém as instruções do executável e do ícone a apresentar. Neste link pode ser encontrado alguns arquivos:

[osx.tar.gz](/files/osx.tar.gz)

Estes arquivos devem ser descompactados no diretório ~/.e/e/applications/engage
Para alterar as informações do arquivo eap deve-se instalar outra ferramenta:

sudo apt-get install eutils

E para alterar um dos arquivos:

e_util_eapp_edit ~/.e/e/applications/engage/gnome-terminal.eap

**Executando**

O executável do engage possui várias opções como tamanho dos ícones, transparência, etc. Eu estou usando o seguinte comando:

engage -G 1 -g 1 -b #00000000 -B #00000000

Sendo:

* -G 1 : capturar os ícones de todos os aplicativos executando
* -g 1 : capturar os ícones de todos os aplicativos minimizados
* -b e -B : cor de fundo e principal. Usando zeros o engage fica completamente transparente

É possível colocar este comando para executar no início da sessão do gnome para que execute automaticamente.

**Funcionamento**

Com o botão esquerdo são executados os aplicativos. Com o botão central sobre um aplicativo aberto todas as janelas deste aplicativo em execução são abertas. Segurando o botão esquerdo sobre o ícone do aplicativo minimizado este é aberto. O botão direito abre o menu de configurações do engage

**Screenshot **

[![engage.jpg](/images/posts/engage.jpg)](/images/posts/engage.jpg)

**Fonte**

[http://www.supriyadisw.net/2006/04/engage-on-dapper-drake](http://www.supriyadisw.net/2006/04/engage-on-dapper-drake)
