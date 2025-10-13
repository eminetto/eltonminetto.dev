---
categories:
- coderockr
comments: true
date: 2013-05-17T00:00:00Z
title: Usando Trello para simular um web service
url: /2013/05/17/usando-trello-para-simular-um-web-service/
---

Essa dica veio do [@leorossetto_ ](https://twitter.com/leorossetto_) e do [@xorna](http://twitter.com/xorna). 

Estamos iniciando o desenvolvimento de um aplicativo para iOS e Android que precisa buscar dados de um servidor remoto, usando a técnica Rest.
Acontece que este serviço vai ser desenvolvido pela equipe do cliente e a [Coderockr](http://coderockr.com) ficou responsável apenas pelo desenvolvimento dos aplicativos móveis.Como o desenvolvimento está sendo feito em paralelo pelas duas equipes, o Leonardo e o André tiveram uma idéia para não dependermos do servidor remoto neste primeiro momento. 
<!--more-->
Eles criaram um arquivo .json com um exemplo dos dados esperados e colocaram como anexo em um Card do [Trello](http://eltonminetto.net/blog/2012/06/27/gerenciando-projetos-com-o-trello/), ferramenta que usamos para gerenciar os nossos projetos:

[![](/images/posts/trello_json_1.png)](/images/posts/trello_json_1.png)

Agora basta acessar o endereço deste arquivo json dentro dos aplicativos e temos um "mock" do serviço. O interessante é que o Trello gera inclusive os cabeçalhos HTTP esperados, como o _Content-Type_. Na imagem abaixo é possível ver o acesso usando o programa [GraphicalHttpClient](https://itunes.apple.com/br/app/graphicalhttpclient/id433095876?mt=12) que uso para testar serviços Rest no Mac.

[![](/images/posts/trello_json_2.png)](/images/posts/trello_json_2.png)

Claro que neste caso só conseguimos emular a requisição do arquivo (método GET) e não criação (POST), alteração (PUT) ou exclusão (DELETE). Também não temos como testar a parte de autenticação do serviço, mas já podemos dar continuidade aos primeiros passos do aplicativo, como a criação dos testes unitários e o acesso aos dados para gravá-los em um banco local, para uso offline. Quando o serviço ficar pronto basta alterarmos a url nos aplicativos e o restante deve funcionar de acordo com o esperado.

Não sei se esse foi um dos usos que a [Frog Creek](http://www.fogcreek.com/) pensou quando desenvolveu essa feature, mas nós acabamos encontrando um uso interessante para ela. 