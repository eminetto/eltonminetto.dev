+++
title = "Checklist Driven Development"
subtitle = ""
date = "2018-10-19T09:00:24+03:00"
bigimg = ""
+++

Confesso que não conhecia o site [http://checklistdrivendevelopment.org/](http://checklistdrivendevelopment.org/) até procurar um título para este post. Mas a leitura do site deu embasamento para o que eu venho fazendo no meu dia a dia, e que vou descrever neste post.

<!--more-->

O fluxo normal de trabalho na [Code:Nation](https://www.codenation.com.br) é:

> issue no Github Issues -> nova branch -> escrever testes -> implementar a lógica -> criar pull request -> testes no servidor de integração contínua -> aprovação do PR -> merge para a branch master -> deploy para servidor de testes -> deploy para ambiente de produção

Apesar de grande parte deste fluxo ser automatizado graças a scripts e ferramentas como o [Drone](http://drone.io) (nosso servidor de integração contínua), continua sendo uma série de passos a serem seguidos. Por isso eu comecei a fazer um ritual pessoal, sempre que assumo uma issue para mim: eu crio um checklist dos cenários que eu preciso escrever testes, das funcionalidades que eu preciso desenvolver, das sub-tarefas que não posso esquecer (atualizar a documentação por exemplo). Com isso eu tenho um panorama de tudo que eu preciso fazer antes mesmo de escrever uma linha de código. E durante o desenvolvimento eu volto a revisar a lista, marcando os pontos que já resolvi e adicionando novos, conforme vão aparecendo, caso necessário. 

Isto tem me ajudado a organizar melhor meu trabalho e também como uma documentação do processo, algo útil para quem estiver entrando no projeto. Também serve como ferramenta auxiliar para guiar a revisão de código e depuração de algum bug que venha a acontecer. 

Uma dica legal é usar o [sistema de templates](https://blog.github.com/2016-02-17-issue-and-pull-request-templates/) que o Github fornece. Você pode criar templates para cada tipo de issues (bugs, tarefas, user stories, etc) e também para os pull requests.

Alguns exemplos de checklists:

[![checklist1](/images/posts/checklist1.png)](/images/posts/checklist1.png) 

[![checklist2](/images/posts/checklist2.png)](/images/posts/checklist2.png)

Esse processo tem funcionado muito bem para mim, tanto para manter o foco no que é importante quanto para ter um histórico do que eu tenho realizado em cada tarefa. Recomendo a tentativa de implementar algo similar em seus projetos.