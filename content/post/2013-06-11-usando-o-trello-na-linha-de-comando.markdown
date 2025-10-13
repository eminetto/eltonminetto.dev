---
categories:
- etc
comments: true
date: 2013-06-11T00:00:00Z
title: Usando o Trello na linha de comando
url: /2013/06/11/usando-o-trello-na-linha-de-comando/
---

Sou um grande fã do [Trello](http://trello.com) conforme já comentei em outros [posts](http://www.google.com/search?q=site%3Aeltonminetto.net&q=trello) aqui no site. 

Uma das características interessantes é a [API](https://trello.com/board/trello-public-api/4ed7e27fe6abb2517a21383d) que nos permite fazermos integrações interessantes com os projetos sendo organizados com ele. Ainda não tive tempo de brincar com a API mas encontrei um projeto que faz uso dela para criar um aplicativo bem interessante, o [trello_cli](https://github.com/brettweavnet/trello_cli). 

É uma _gem_ para a linguagem Ruby que nos permite acessar o Trello via linha de comando. Qual a importância disto? Poderíamos usar, por exemplo, o comando em um shell script ou mesmo em um programa para criar novas tarefas em um board caso aconteça algum erro no sistema, ou outras ações. 
<!--more-->

## Instalação

Instalei tanto no MacOSX quanto no Ubuntu e em ambos os casos funcionou perfeitamente.  No caso do Ubuntu precisei antes instalar o pacote de desenvolvimento do Ruby com o comando:

```
sudo apt-get install ruby1.9.1-dev
```

Depois bastou instalar a _gem_:

```
gem install trello_cli
```

O próximo passo é requisitar as chaves de acesso para a API do Trello. Após fazer login no Trello é preciso acessar a url abaixo, no seu navegador:

```
https://trello.com/1/appKey/generate
```
E você vai ver uma tela como esta:

[![](/images/posts/trello_api.png)](/images/posts/trello_api.png)

Com essa chave de API você pode solicitar seu token de acesso. É possível requisitar o token de leitura ou o de leitura e gravação. Neste exemplo vamos usar o de leitura/gravação para podermos criar novos itens no Trello. 
Para isso é preciso acessar a url abaixo

```
https://trello.com/1/authorize?key=YOUR_API_KEY&name=trello-cli=1day&response_type=token&scope=read,write
```

Colocando a sua chave na parte da url _key=YOUR_API_KEY_

Agora precisamos fazer o último passo, que é configurar o ambiente para reconhecer a sua chave de API e o seu token. No caso do Linux e do MacOSX é possível fazer isso alterando o arquivo _.bash_profile_ do diretório _home_ do seu usuário. Coloquei o seguinte no final do meu arquivo _~/.bash_profile_:

```
export TRELLO_DEVELOPER_PUBLIC_KEY=api_key
export TRELLO_MEMBER_TOKEN=member_token
```

É preciso reiniciar o Terminal para que as variáveis tenham efeito. Ou executar o comando:

```
. ~/.bash_profile
```

## Usando 

O formato de uso é o seguinte:

```
trello TARGET COMMAND OPTIONS
```

Você pode usar a ajuda do comando também:

```
trello -h
```

## Exemplos

Mostrar todas as listas que você tem acesso:

```
trello board list
```

O resultado do comando acima vai ser algo parecido com isso:

```
Projeto de Exemplo ( 5198e84a2123fsdyhe00e466 )
```

Esse valor entre parênteses é o ID da lista. Podemos agora mostrar todos os boards dessa lista:

```
trello list list -b 5198e84a2123fsdyhe00e466
```

E o resultado é algo como:
```
Backlog ( 5198e8jahb41ka2918008d05 )
To Do ( 5198e84a2781jabrje00e467 )
Doing ( kab5ah4a278e83fd7e00e468 )
Review ( 5198e87c7l1j6hab700087a5 )
Done ( 5198e84abah4hab57e00e469 )
```

Podemos criar um novo card em um dos boards acima com o comando:

```
trello card create -b 5198e84a2123fsdyhe00e466 -l 5198e84a2781jabrje00e467 -n 'Nome do card' -d 'Descrição do Card'
```

Sendo _-b_ o id do board e _-l_ o id da lista.

Eu consigo imaginar um shell script que verifique a existência de algum erro no arquivo de log do Apache, por exemplo, e crie um novo card na lista "To Do" para que alguém da equipe resolva o bug e mova o card para o "Done".  As possibilidades são grandes.

A próxima brincadeira que quero fazer é com a própria API. Talvez criar algo para adicionar cards via PHP ou JavaScript. Diversão a frente! 