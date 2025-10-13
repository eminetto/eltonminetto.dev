---
categories:
- coderockr
- codes
- etc
comments: true
date: 2011-07-19T15:00:25Z
slug: news-feed-usando-nodejs-pubsub-io-e-jquery
title: News feed usando nodeJS, Pubsub.io e jQuery
url: /2011/07/19/news-feed-usando-nodejs-pubsub-io-e-jquery/
wordpress_id: 778
---

Um dos projetos que estamos desenvolvendo na [Coderockr](http://www.coderockr.com) é uma rede social, da qual vamos dar mais detalhes no futuro, e um dos componentes é um news feed, algo parecido com o "mural do Facebook" onde são mostrados os eventos mais atuais aos usuários (novas notícias, novos pedidos de amizade, novos conteúdos, etc).
Estamos estudando algumas tecnologias para melhor solucionar esta necessidade, e uma das soluções é algo bem novo para nós, o uso quase que total de Javascript: [nodeJS](http://nodejs.org), [Pubsub.io](http://pubsub.io/) e nossa velha amiga jQuery.
O nodeJS é uma das tecnologias mais interessantes que surgiu nos últimos tempos. O mago do Javascript  [@jaydson](http://twitter.com/jaydson) escreveu alguns posts muito legais fazendo uma [introdução](http://jaydson.org/nodejs-introducao) e mostrando os [primeiros passos](http://jaydson.org/nodejs-instalacao) na ferramenta. Recomendo a leitura.
O Pubsub.io é construído sobre o nodeJS e é um "query based message hub", trabalhando com o conceito de publishers (programas que publicam conteúdo) e consumers (os que consumem as mensagens), e adicionando a possibilidade de usar uma query language baseada na usada pelo banco NoSQL MongoDB para filtrar os resultados.
A solução que estamos testando funciona da seguinte forma: sempre que um evento acontece (uma nova foto é salva, por exemplo) o componente que executou o evento (um model ou um controller de uma aplicação Zend Framework, por exemplo) faz uma requisição http para uma url servida pelo nodeJS e este publica uma mensagem no Pubsub.io. Na aplicação client, no navegador web, usando a biblioteca JS do Pubsub.io e o jQuery mostramos as mensagens na tela. Vamos tentar explicar com códigos :)
Após instalar o nodeJS e o npm ([node packet manager](http://howtonode.org/introduction-to-npm)) é preciso instalar o Pubsub.io, usando o comando abaixo, no mesmo diretório onde ficará o script server.js (script nodeJS mostrado abaixo):
`npm install pubsub.io`
Agora é preciso instalar e executar o servidor do Pubsub.io:
`
git clone git@github.com:pubsubio/pubsub-hub.git
./pubsub-hub/lib/server.js
`
Ele ficará ouvindo na porta 9999
O próximo passo é escrever o [código do aplicativo](https://gist.github.com/1093224) do nodeJS e executá-lo com o comando:
`node server.js`
Como o nodeJS fica executando na porta 8080 podemos publicar novos eventos usando algo simples como um comando curl, via linha de comando:
`curl -d "title=new photo&detail=http://localhost/photo/id/1&user=eminetto" http://127.0.0.1:8888`
O -d indica que estamos usando o comando POST para enviar. 
Podemos também usar o curl no PHP:
`
$url = 'http://127.0.0.1:8888';
$postvars = 'title=new image&detail=http://localhost/image/id/1&user=eminetto';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST      ,1);
curl_setopt($ch, CURLOPT_POSTFIELDS    ,$postvars);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1); 
curl_setopt($ch, CURLOPT_HEADER      ,0);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
$Rec_Data = curl_exec($ch);
`
Na parte client vamos usar a biblioteca Javascript do Pubsub.io e jQuery para mostrar ao usuário a notificação: [ver código](https://gist.github.com/1093252)

No site do Pubsub.io é possível ver outras features importantes como a possibilidade de usar autenticação para garantir a segurança, queries avançadas e a possibilidade de usar o MongoDB para armazenar as mensagens.
Gostei muito da solução. Ainda falta testar coisas como performance, escalabilidade mas me parece ter um bom futuro. 
