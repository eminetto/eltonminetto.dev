---
categories:
- apple
comments: true
date: 2011-10-24T19:08:17Z
slug: usando-o-growlnotify-para-gerar-notificacoes-no-mac-osx
title: Usando o growlnotify para gerar notificações no Mac OSX
url: /2011/10/24/usando-o-growlnotify-para-gerar-notificacoes-no-mac-osx/
wordpress_id: 802
---

O [Growl](http://growl.info) é uma das ferramentas mais úteis que tenho instalado no Mac OSX. Ele é uma central de notificações que é usada por vários aplicativos para mostrar informações na sua tela. Twitter, iTunes, Last.fm, Adium, Skype são apenas alguns exemplos de ferramentas que usam o Growl para informar o que está acontecendo aos seus usuários.
Um dos [extras](http://growl.info/extras.php) que estão disponíveis, na forma de plugins, é o [growlnotify](http://growl.info/extras.php#growlnotify). Trata-se de uma ferramenta de linha de comando que você pode usar nos seus scripts para lhe informar de eventos que ocorrerem. 
Para instalar basta fazer o [download](http://growl.info/downloads.php) do .dmg, montá-lo e seguir as instruções na tela.

Um exemplo de uso:

`phpunit ; growlnotify -m "Fim do build"`

Quando o build do phpunit terminar uma mensagem é mostrada na tela:
[![](/images/posts/notify_150.png)](/images/posts/notify.png)

Existem outras opções como mudar o ícone, forçar a notificação para que não desapareça automaticamente, mudar a prioridade, etc. Além disso, como o Growl é um servidor de rede é possível mandar notificações para outras máquinas.
Uma lista de opções do comando está disponível no help:
`growlnotify --help` 
Até a versão 1.2 o Growl era gratuíto, mas a partir da 1.3 ele está disponível por U$ 1.99 na Apple Store. Mas é um investimento que valhe a pena. 
