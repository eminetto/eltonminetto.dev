---
title: "Gerenciando tarefas na linha de comando"
date: 2018-04-12T17:06:24-03:00
draft: false
---
A linha de comando é vida! É a interface definitiva!

Passado o momento fanboy quero apresentar outra ferramenta que venho usado e gostando bastante. Quem me indicou o [todolist](http://todolist.site/) foi o [Stamatios Stamou Jr](https://www.linkedin.com/in/ssjunior/), fundador e CEO da startup [Pipz](http://pipz.com/br/).

<!--more-->

Trata-se de um aplicativo escrito em Go que pode ser usado em qualquer sistema operacional, graças a facilidade da linguagem em gerar binários para vários sistemas. Outra vantagem é que ele usa um simples arquivo no formato JSON para armazenar suas tarefas. Isso facilita o backup, compartilhamento entre ambientes (uso um diretório no Dropbox para isso) e processamento dos seus dados da forma como você quiser. É muito fácil processar um arquivo JSON, em qualquer linguagem de programação moderna. 

Para instalar a ferramenta depende do sistema operacional, mas todas as [alternativas](http://todolist.site/#installation) são muito fáceis. No macOS basta um:

	brew install todolist

O próximo passo é inicializar o arquivo que será usado para salvar suas tarefas com o comando:

	todolist init

O arquivo *.todos.json* é criado no seu diretório home. Um passo extra que eu fiz foi mover o arquivo para um diretório do Dropbox e criar um link simbólico:

	mkdir ~/Dropbox/todolist
	mv ~/.todos.json ~/Dropbox/todolist/todos.json
	ln -s ~/Dropbox/todolist/todos.json ~/.todos.json

O site oficial possui [uma documentação](http://todolist.site/#initializing) bem completa, além do help da própria ferramenta ser muito útil. Digitando apenas *todolist* é possível ver os principais comandos cenários. 

Fazendo um resumo rápido, segue alguns exemplos de comandos que eu uso diariamente.


	todolist a +codenation fazer backup do servidor do mautic @devops due tom

O comando acima cria uma tarefa chamada *fazer backup do servidor do mautic* no projeto *codenation* com o contexto *devops*. 

Posso listar as tarefas com o comando

```
17:21:47 in ~ ⇡94% ➜ todolist l

 all
 11	[ ]	 		configurar site @devandmusic
 4	[ ]	 		revisar finanças família
 6	[ ]	 		escrever @post sobre o todolist
 17	[ ]	 		escrever @post sobre organização do iphone
 16	[ ]	 		marcar oftalmologista
 31	[ ]	 		+coderockr  1-1 com Raony
 29	[ ]	 		+coderockr  1-1 com Renata
 32	[ ]	 		+coderockr  1-1 com Leandro
 26	[ ]	 		comprar passagens ccxp
 25	[ ]	 		comprar ingressos ccxp
 10	[ ]	Fri Apr 6	marcar dentista
 33	[ ]	tomorrow	+codenation fazer backup do servidor do mautic @devops
```

Eu prefiro agrupar as tarefas por contexto ou projeto, com os comandos:

```
17:22:46 in ~ ⇡94% ➜ todolist l by p

 No projects
 11	[ ]	 		configurar site @devandmusic
 4	[ ]	 		revisar finanças família
 6	[ ]	 		escrever @post sobre o todolist
 17	[ ]	 		escrever @post sobre organização do iphone
 16	[ ]	 		marcar oftalmologista
 26	[ ]	 		comprar passagens ccxp
 25	[ ]	 		comprar ingressos ccxp
 10	[ ]	Fri Apr 6	marcar dentista

 codenation
 33	[ ]	tomorrow	+codenation fazer backup do servidor do mautic @devops

 coderockr
 31	[ ]	 	+coderockr  1-1 com Raony
 29	[ ]	 	+coderockr  1-1 com Renata
 32	[ ]	 	+coderockr  1-1 com Leandro
 
```

```
17:25:07 in ~ ⇡95% ➜ todolist l by c

 No contexts
 4	[ ]	 		revisar finanças família
 16	[ ]	 		marcar oftalmologista
 31	[ ]	 		+coderockr  1-1 com Raony
 29	[ ]	 		+coderockr  1-1 com Renata
 32	[ ]	 		+coderockr  1-1 com Leandro
 26	[ ]	 		comprar passagens ccxp
 25	[ ]	 		comprar ingressos ccxp
 10	[ ]	Fri Apr 6	marcar dentista

 devandmusic
 11	[ ]	 	configurar site @devandmusic

 devops
 33	[ ]	tomorrow	+codenation fazer backup do servidor do mautic @devops

 post
 6	[ ]	 	escrever @post sobre o todolist
 17	[ ]	 	escrever @post sobre organização do iphone
```

Para completar uma das tarefas é só executar:

	todolist c 6

E é possível arquivar as tarefas completas:

	todolist ac

Existem outras opções como edição, quebrar uma tarefa em sub-tarefas, adicionar notas, listar as atrasadas, etc. Vale a pena ler a documentação e brincar com a ferramenta para aprender os seus truques.

Outra dica é criar alias para os comandos mais usados, como:

```
alias t="todolist"
alias ta="todolist a"
alias tl="todolist l by c"
alias tc="todolist c"
```

Estou gostando bastante da ferramenta e das possibilidades que ela adiciona ao meu dia a dia. Espero que seja útil para mais alguém. 