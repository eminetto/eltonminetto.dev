+++
title = "Modelando sistemas usando Event Storming"
subtitle = ""
date = "2017-10-13T10:54:24+02:00"
bigimg = ""
+++

O desenvolvimento de software envolve muito mais do que escrever códigos. O objetivo maior é desenvolver o software correto, que resolva as necessidades do usuários e com a máxima qualidade.

Parte deste desafio é aumentar o entendimento do negócio e dos problemas que estão sendo resolvidos, por todos os envolvidos no projeto.

Existem varias formas de realizar este levantamento de requisitos, desde as mais tradicionais até ideias modernas. Neste post vou apresentar uma destas metodologias, o Event Storming.

<!--more-->

### O que é

Uma técnica de modelagem e discussão em grupo, rápida, divertida e que tem como
objetivo acelerar o conhecimento sobre um projeto, negócio ou mesmo uma
funcionalidade complexa.

Ela foi criada por um consultor italiano chamado [Alberto
Brandolini](https://www.linkedin.com/in/brando/) e sua primeira referência é de
um [post publicado em
2013](http://ziobrando.blogspot.com.br/2013/11/introducing-event-storming.html).

### O que não é

Apesar de ter vários conceitos em comum com DDD (Domain Driven Design) e Event
Sourcing, o Event Storming não é voltado apenas para desenvolvimento de
software. A ideia é que todos os envolvidos participem, para gerar uma linguagem
que possa ser usada tanto pelos desenvolvedores quanto pelos detentores do
conhecimento de negócios.

Não estamos discutindo sobre implementação então, mais tarde, a equipe de
desenvolvimento pode selecionar a melhor arquitetura de código necessária para
obter os resultados esperados.

### Conceitos

Primeiro é necessário entendermos os conceitos básicos que vamos usar nos
próximos passos. São eles:

#### Events

Um evento de domínio é algo que aconteceu e que tem relevância para o projeto
sendo analisado. Eles capturam fatos que ocorreram, independente da forma como
serão gerados ou tratados.

#### Commands

Os comandos são ações que ocorrem no sistema, os geradores dos eventos.

#### Actors

Os atores são os responsáveis por executar os comandos, que por sua vez geram os
eventos. Como atores podemos considerar os usuários do sistema, sistemas
externos ou até mesmo o tempo (um comando que é gerado em determinada data do
mês, por exemplo).

#### Agreggate

Um agreggate é um agrupamento lógico de eventos e comandos que estão
relacionados.

#### Bounded context

Representam os limites do sistema, agrupando agreggates e identificando como
estes limites se comunicam. Podemos fazer uma analogia com os “módulos” de um
sistema.

### O workshop

Entendidos os conceitos principais podemos falar sobre o Event Storming
propriamente dito. Trata-se de uma atividade em grupo onde juntamos
desenvolvedores, designers, analistas de negócios, usuários do sistema, etc.

[![](https://cdn-images-1.medium.com/max/800/1*BzPZVpXJVDDXFuNlknwXEg.jpeg)](https://cdn-images-1.medium.com/max/800/1*BzPZVpXJVDDXFuNlknwXEg.jpeg) 


O que é necessário:

* Post-its de cores diferentes, para diferenciar Events, Commands, Actors e
Agreggates, etc.
* Um espaço físico com uma parede disponível para os post-its serem colados. E sem
cadeiras, a ideia é todos ficarem em pé discutindo, colando e movendo os
post-its.
* Um mediador que vai ficar guiando o andamento e controlando o tempo.

### Os passos

**1.** São identificados os eventos que ocorrem, independente de ordem ou de quem os gera. São anotados em post-its de uma cor selecionada e colados na parede. Os nomes dos eventos devem ser escritos no passado, como *Usuário denunciado*,
*Conta criada*.

[![](https://cdn-images-1.medium.com/max/800/1*fqvkmy2y2g7DYOVwoIad0w.jpeg)](https://cdn-images-1.medium.com/max/800/1*fqvkmy2y2g7DYOVwoIad0w.jpeg) 


**2.** São identificados os comandos que geram os eventos, anotados e colados próximos aos respectivos eventos. Os nomes dos comandos devem ser no formato *Denunciar usuário*, *Submeter formulário de cadastro*.

**3.** São identificados os atores que executam os comandos. Os atores são anotados em post-its de uma cor selecionada e posicionados ao lado de cada comando.

[![](https://cdn-images-1.medium.com/max/800/1*bx_T0Ku7FkDEw-Uee0Sf5g.jpeg)](https://cdn-images-1.medium.com/max/800/1*bx_T0Ku7FkDEw-Uee0Sf5g.jpeg) 


**4.** Agrupar os eventos e comandos em agreggates, movendo os post-its para posicioná-los próximos e dando um nome para cada grupo

[![](https://cdn-images-1.medium.com/max/800/1*AQvNMrDUcgb9ZLXw59qf9Q.jpeg)](https://cdn-images-1.medium.com/max/800/1*AQvNMrDUcgb9ZLXw59qf9Q.jpeg) 

**5.** Identificar os Bounded contexts movendo os agreggates e identificando quais eventos fazem a ligação entre eles. Dependendo da complexidade do projeto ou mesmo da funcionalidade sendo analisada é possível que este passo não seja necessário.

Uma sugestão para o mediador é definir um tempo específico para cada passo, geralmente 20 a 30 minutos.

### Os resultados

Usamos um dos eventos internos da Coderockr, o Coderock Jam, para executar uma sessão de Event Storming. Ao final do processo conseguimos ter alguns resultados interessantes:

* Tivemos uma melhor compreensão dos eventos mais importantes que ocorrem. Durante as discussões várias vezes levantamos a pergunta “isso é um evento relevante?” antes de decidir se deveríamos ter um post-it na parede.
* Conseguimos identificar os limites dos módulos do sistema, graças aos Aggregates
* Foi mais fácil identificar o que seria prioridade no desenvolvimento, o que ajuda na definição de um roadmap
* Durante as discussões encontramos termos que eram dúbios e a equipe chegou a uma nomenclatura que todos acordaram. Isso é muito importante no desenvolvimento, para evitar confusão de conceitos e nomes
* Identificamos novas funcionalidades que podemos implementar em um futuro próximo e que não estavam no nosso radar antes da discussão
* A equipe interagiu bastante, gerando mais entrosamento e confiança

Alguns pontos a considerar no nosso caso:

* Foi a primeira sessão que fizemos de Event Storming, então pode ser que não tenhamos percebido alguns erros no processo
* Foi a primeira vez que mediei um Event Storming, então percebi alguns pontos que devo melhorar
* Usamos como case um projeto que estamos criando na Coderockr então agimos como desenvolvedores e também como “analistas de negócios”. Talvez esse não seja o cenário ideal, mas pretendemos repetir o processo em outros projetos, incluindo mais pessoas de diferentes áreas.

Nos próximos eventos internos da Coderockr vamos testar outras metodologias para podermos comparar os resultados e também postá-los aqui. Sabemos que o Event Storming não é uma “bala de prata” que vai resolver todos os nossos problemas, mas é uma ferramenta a mais no nosso arsenal. E foi uma experiência bem
interessante e que recomendo a tentativa.

### Links

[https://blog.redelastic.com/corporate-arts-crafts-modelling-reactive-systems-with-event-storming-73c6236f5dd7](https://blog.redelastic.com/corporate-arts-crafts-modelling-reactive-systems-with-event-storming-73c6236f5dd7)

[https://techbeacon.com/introduction-event-storming-easy-way-achieve-domain-driven-design](https://techbeacon.com/introduction-event-storming-easy-way-achieve-domain-driven-design)

[http://ziobrando.blogspot.com.br/2013/11/introducing-event-storming.html](http://ziobrando.blogspot.com.br/2013/11/introducing-event-storming.html)
