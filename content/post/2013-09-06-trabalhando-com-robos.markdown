---
categories:
- devops
comments: true
date: 2013-09-06T00:00:00Z
title: Trabalhando com robôs
url: /2013/09/06/trabalhando-com-robos/
---

Uma das coisas mais divertidas de ser programador é a possibilidade de automatizar tarefas repetitivas, deixando nosso cérebro livre para pensar em [algo mais útil](https://httpcats.herokuapp.com). Isso, aliado a minha curiosidade pelo assunto ["DevOps"](http://eltonminetto.net/blog/2013/06/23/cultura-devops/) está me levando a estudar algumas coisas divertidas como o [Hubot](http://hubot.github.com). 

<!--more-->

O Hubot é mais uma das invenções do Github, uma das empresas que mais admiro. Trata-se de um aplicativo que automatiza tarefas e funciona integrado a algum sistema de chats como o Campfire ou o [Hall](http://eltonminetto.net/blog/2013/08/29/chat-corporativo/).  As tarefas são scripts feitos em CoffeeScript, enquanto o próprio Hubot é feito em NodeJS.

A instalação do Hubot é simples, sendo que ele depende apenas do NodeJS e do npm, para instalar os pacotes extras. A [documentação oficial](https://github.com/github/hubot/tree/master/docs) é bem intuitiva e útil, então vou pular esta parte neste post, focando em explicar como usamos na Coderockr.

Depois de seguir os passos da instalação é possível escolher algum dos diversos scripts (tarefas) [disponíveis](http://hubot-script-catalog.herokuapp.com) ou criar as [suas próprias](https://github.com/github/hubot/blob/master/docs/scripting.md). 

Alguns scripts úteis que estamos usando:

- bitly: encurta uma url
- aws: retorna o status dos nossos servidores na Amazon
- email: envia um e-mail direto do chat
- newrelic: verifica o status dos nossos servidores no newrelic
- phpdoc: retorna a documentação de uma função do PHP
- pomodoro: gerencia (inicia, para, contabiliza) pomodoros
- remind: emite uma mensagem no tempo especificado
- github-activity: mostra a atividade (commits, issues) de um determinado repositório
- trello: lista cards de um board e permite criar novos cards 

E alguns inúteis:

- dance: mostra uma imagem do [Carlton dançando](http://gifsoup.com/webroot/animatedgifs/131815_o.gif)
- klout: mostra a pontuação de um usuário no Klout
- mustache: encontra uma imagem no Google e coloca um bigode na foto
- rotten:  usa o site Rotten Tomatoes para mostrar informações de lançamento de filmes e pontuações
- youtube: encontra um video e mostra no chat


Quando você instala o Hubot a primeira coisa que você configura é o nome do seu robô. O da Coderockr chama-se "break" e tem até foto:

[![](/images/posts/break.jpg)](/images/posts/break.jpg)

O desenho foi feito pelo [Mateus Guerra](https://www.facebook.com/mateusg), programador PHP e desenhista nas horas vagas. Ele fez durante um dos [short breaks](http://en.wikipedia.org/wiki/Pomodoro_Technique), por isso o nome.  

Depois de instalado é preciso que o seu robô seja hospedado em algum lugar, algum servidor público ou privado. Estamos usando a conta free do Heroku para fazer isso, porque a integração com o [Hubot e o Heroku](https://github.com/github/hubot/blob/master/docs/deploying/heroku.md) é muito boa e fácil de usar.  

O próximo passo é configurar a integração entre o Hubot e o Hall, seguindo [essa documentação](https://github.com/Hall/hubot-hall).  Depois é só adicionar o usuário do seu robô nas salas de chat e chamar ele, como nos exemplo:

```php
break help
break ec2 status
break mustache me elton minetto
```

Agora vamos começar a incluir novos scripts para fazer coisas como deploy de aplicativos, monitorar a integração contínua, etc, até ensinarmos um monte de truques ao nosso novo mascote.




 

 