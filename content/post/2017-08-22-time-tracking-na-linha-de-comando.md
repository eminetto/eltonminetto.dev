+++
title = "Time tracking na linha de comando"
subtitle = ""
date = "2017-08-22T08:23:24-03:00"
bigimg = ""
+++

Escrevi [um post](http://eltonminetto.net/2014/11/26/multitasking-so-e-bom-no-seu-computador/) algum tempo atrás falando sobre as armadilhas do "multitasking" no nosso dia a dia. Uma das minhas sugestões no post foi usar uma ferramenta para anotar as horas e tarefas, o que ajuda a manter o foco em uma coisa de cada vez. Neste post vou apresentar uma nova ferramenta que estou usando e gostando bastante, o Watson.

<!--more-->

Eu passo a maior parte do meu tempo na frente do computador com um terminal aberto. Quem me conhece sabe que não sou fã de IDEs e uso o terminal para fazer tudo, desde pesquisa em arquivos (*grep, fgrep, find, ack*) até executar testes unitários (*PHPUnit, go test*) e deploys (*shell script*). Nada mais natural que eu acabasse usando a linha de comando para monitorar o meu tempo também. Em uma das minhas leituras diárias no Hacker News esbarrei com uma ferramenta interessante, o [Watson](https://tailordev.github.io/Watson/).

O funcionamento dele é bem simples. Após a [instalação](https://tailordev.github.io/Watson/#installation) basta iniciar o trabalho usando o comando *start*, como no exemplo:

	watson start coderockr +"Criacao de Post"

Sim, estou usando o Watson para monitorar o tempo que estou dedicando para escrever este post!

O parâmetro *coderockr* é o nome do projeto e o *+"Criacao de Post"* é a tag que estou atribuindo ao trabalho. É possível colocar mais tags, seguidas de espaços como:

	watson start coderockr +"Criacao de Post" +Medium +Produtividade

Para visualizar todos os seus projetos basta executar:

	watson projects

E as tags com o 

	watson tags

Também é possível ver o status do trabalho que está realizando agora:

	watson status
	Project coderockr [Criacao de Post, Elton Minetto] started 9 minutes ago (2017.08.22 08:23:56-0300)

E ver o *log* dos seus trabalhos recentes com o :

	watson log

A saída do comando *log* vai mostrar um identificador do trabalho, algo bem parecido com um *commit* do *Git*. Com esse identificador é possível editar determinado trabalho:

	watson edit 66843a7

O seu editor padrão vai ser aberto com um arquivo *json* contendo as informações do trabalho, que pode ser alterado e salvo. 

Para salvar o histórico dos trabalhos existe duas soluções. A mais simples é configurar uma variável de ambiente indicando para o Watson onde salvar seus arquivos, o que pode ser em um diretório do Dropbox, iCloud, ou mesmo Git. Eu configurei desta forma:

	WATSON_DIR=/Users/eminetto/Dropbox/watson

A segunda forma é usar um servidor remoto para armazenar o seu log. O Watson tem um projeto complementar, o [Crick](https://github.com/TailorDev/crick). Com ele é possível sincronizar os seus logs e também  visualizar de maneira gráfica como está sendo usado o tempo em cada projeto. 

[![crick](/images/posts/crick.png)](/images/posts/crick.png) 

O Crick é um projeto open source que pode ser hospedado em um servidor próprio mas também existe uma [solução na nuvem](https://crick.io/) que pode ser usada tranquilamente. O Crick também permite a criação de times, o que facilita o seu uso em grupo. Depois de criada a conta no http://crick.io, ou no servidor próprio, basta [configurar o Watson](https://tailordev.github.io/Watson/user-guide/configuration/#available-settings) para usar o backend do Crick para sincronizar os dados. Depois de configurado basta executar o comando:

	watson sync

Desta forma os logs são enviados para o servidor e ficam disponíveis via interface gráfica. 

A grande vantagem do Watson é podermos usá-lo em automatizações e scripts, facilitando bastante o dia a dia do desenvolvedor e evitando o multitasking. E o Crick ajuda bastante na tarefa de visualizar de maneira mais rápida e prática como estamos consumindo nosso tempo, o que ajuda na otimização de tarefas. 

E tudo isso sem sair do nosso amado terminal ;)