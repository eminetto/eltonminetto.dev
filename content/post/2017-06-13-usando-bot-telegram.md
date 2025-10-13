+++
bigimg = ""
date = "2017-06-13T12:30:24+02:00"
subtitle = ""
title = "Usando um Bot do Telegram para receber notificações"

+++

Quem me conhece sabe que eu não gosto muito do WhatsApp. E o motivo principal nem é a quantia de mensagens "bonitinhas" que seus parentes enviam todos os dias. O meu principal problema, pelo menos por enquanto, é a falta de opções para nós desenvolvedores criarmos automações e integrações.

Neste post vou mostrar um exemplo simples mas que está sendo bem útil para mim. Trata-se do [Integram](http://integram.org/)

<!--more-->

Com o Integram é possível integrar o Telegram com vários serviços como Trello, Gitlab, Bitbucket, etc. E por ser um projeto Open Source qualquer pessoa pode sugerir melhorias ou submeter novas integrações. 

Mas ele tem uma função ainda mais simples. Basta, no seu Telegram, seguir o usuário @bullhorn_bot. Ao fazer isso vai aparecer na tela a opção "start", ou basta digitar o texto /start que o Bot vai criar uma URL única para você. Algo como: https://integram.org/asoijajsaioa

Com esta URL é possível enviar mensagens para o seu novo amigo, o Horn Bot. É só enviar uma requisição POST com o conteúdo: 

```javascript
{"text":"So _advanced_\nMuch *innovations* 🙀"}
```

No exemplo acima, o que o próprio Bot apresenta, é possível ver que ele suporta Emoji. Afinal, do que seria o mundo sem Emojis, certo?

Eu estou usando este bot para receber notificações de deploys em servidores, scripts demorados executando na minha máquina (npm install por exemplo...). É possível usar qualquer linguagem de programação ou o bom e velho CURL:

	curl -H "Content-Type: application/json" -X POST -d '{"text":"Deploy Codenation Finalizado"}' https://integram.org/asoijajsaioa
	

Este é só um exemplo bem simples, mas útil, do que podemos fazer com o Telegram. Até onde eu sei este tipo de integração não é possível com o WhatsApp, mas o Facebook Messenger e o Apple iMessage suportam algo similar. 

Como desenvolvedores, só nos resta esperar que algum dia o WhatsApp implemente algo similar ou que as pessoas comecem a usar algo melhor como o Telegram :)

