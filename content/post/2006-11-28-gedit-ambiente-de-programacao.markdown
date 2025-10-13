---
categories:
- home
- python
- ubuntu
comments: true
date: 2006-11-28T10:35:25Z
slug: gedit-ambiente-de-programacao
title: None
url: /2006/11/28/gedit-ambiente-de-programacao/
wordpress_id: 175
---

Quanto assisti aos [screencasts](http://www.rubyonrails.org/screencasts) do Ruby on Rails uma das coisas que mais me chamou atenção foi o [TextMate,](http://macromates.com/) o editor usado na apresentação. Fiz uma [pesquisa](http://macromates.com/blog/archives/2005/11/07/windowslinux-alternative/) na Internet e aparentemente a ferramenta já virou Cult entre os programadores. O problema é que ela só existe para MacOS e os desenvolvedores não tem previsão nenhuma de lançarem alguma versão para Linux. Além disso ela é comercial.

Tentei achar alguma alternativa para Linux. Inicialmente testei o Scite, ferramenta baseada no Scintilla. Muito flexível, mas complexa de configurar e pouco integrada ao resto do desktop. Foi quando lembrei que o Gedit agora tem um sistema de plugins.

Instalei os plugins e assim consegui customizar um ambiente de programação interessante. Para instalar:

sudo apt-get install gedit-plugins

Depois de instalado é possível ir em Editar->Preferências->Plugins e escolher quais utilizar. Os que eu estou usando:

- Bracket Completion: adiciona automaticamente as chaves para fechamento de funções e blocos

- Code comment: comenta um trecho de código (Ctrl+M) ou descomenta (Shift+Ctrl+M)

- Console python: adiciona um console do Python no painel inferior. Muito útil

- Ferramentas externas: pode-se configurar teclas de atalho para executar um compilador, navegador, etc

- Terminal embutido: adiciona um terminal no painel inferior. Dentro do terminal é só digitar gedit arquivo que o arquivo é aberto em uma nova aba do editor. Para facilitar ainda mais eu criei um alias no meu .bashrc para quando digitar o comando g arquivo ele substitui pelo gedit arquivo. Soi só adicionar a linha g='gedit' no fim do .bashrc

- Trechos: essa é muito legal. Você pode definir pedaços de código a serem adicionados automaticamente. Por exemplo. Editando um arquivo php é só digitar foreach e teclar o Tab que um "esqueleto" do comando foreach é gerado na tela. Além disso pode-se teclar Ctrl+Space para mostrar uma lista de trechos configurados. Tudo isso configurável na tela de configuração do plugin

Isso tudo adicionado ao Syntax Highlighting que o Gedit já faz para as principais linguagens de programação e temos um ambiente integrado e simples de usar. Lógico que ainda dá para elencar algumas coisas que faltam, mas com esta arquitetura de plugins é só criarmos ou esperarmos alguém desenvolver.

Uma imagem do meu Gedit configurado:

[![Gedit](/images/posts/gedit1.png)](/images/posts/gedit1.png)
