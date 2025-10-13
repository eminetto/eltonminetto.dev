---
title: Sobre a talk Building Systems that Last do Werner Vogels
date: 2025-08-31T17:30:00-03:00
draft: false
---
Na última semana aconteceu aqui em Florianópolis um evento que eu tenho bastante carinho. Trata-se do [Startup Summit](https://www.startupsummit.com.br), que atualmente é o maior evento sobre o assunto no Brasil. Tenho boas lembranças pois ajudei a organizar a trilha de tecnologia da edição de 2019. 

Nesta edição um dos palestrantes era ninguém menos do que o grande [Werner Vogels](https://allthingsdistributed.com/about.html), atualmente CTO da Amazon. Sempre fui grande fã dos seus feitos e conteúdos, então poder ver ele ao vivo era algo que eu não poderia perder. 

[![werner1](/images/posts/werner1.png)](/images/posts/werner1.png)

Este post é um resumo de alguns insights que eu anotei durante a palestra, junto com alguns pensamentos que elocubrei sobre os slides.

[![werner2](/images/posts/werner2.png)](/images/posts/werner2.png)

Ele começou falando sobre *Evolvability*, a habilidade de um sistema poder acomodar novas features conforme ele evolui em seu ciclo de vida. Para isso precisamos pensar no design das aplicações, nos trade-offs que escolhemos. Ele não citou isso, mas uma contribuição que posso fazer é a indicação do livro [Building Evolutionary Architectures](https://a.co/d/gouBHXp), que é uma ótima referência sobre esse assunto.

[![werner3](/images/posts/werner3.png)](/images/posts/werner3.png)

Essa frase é impactante: 

> Tudo falha, o tempo todo. 

Ela me lembrou outra frase muito parecida, de outro ídolo meu, o [Bruno Ghisi](https://www.linkedin.com/in/brunoghisi/), co-founder da Resultados Digitais. Ele falou em uma palestra que vi alguns anos atrás:

> Na escala tudo quebra

Mas o ponto que o Werner reforçou foi que ao pensarmos desta forma podemos nos preparar para a falha, construindo arquiteturas que sejam resilientes. Também me lembrou outra fala, do Sam Newman, sobre a qual eu [escrevi aqui no site](https://eltonminetto.dev/post/2023-01-18-programacao-pessimista/) algum tempo atrás.  

[![werner4](/images/posts/werner4.png)](/images/posts/werner4.png)

Uma abordagem que pode ser usada para criar aplicações mais resilientes é a “Cell-based architecture”. A própria AWS tem um [material](https://docs.aws.amazon.com/wellarchitected/latest/reducing-scope-of-impact-with-cell-based-architecture/reducing-scope-of-impact-with-cell-based-architecture.html) bem interessante sobre o conceito, que não foi citada na palestra mas que eu já tinha anotado aqui na minha lista de leituras ;)

[![werner5](/images/posts/werner5.png)](/images/posts/werner5.png)

Outro tópico bem interessante foi sobre “The Frugal Architect”. Segundo o site do projeto (tradução minha):

> Leis simples para construir arquiteturas modernas, sustentáveis e com baixo custo.

No site é possível ver [detalhes](https://thefrugalarchitect.com/laws/) sobre as “leis”, bem como [cases](https://thefrugalarchitect.com/architects/) e até um [podcast](https://thefrugalarchitect.com/podcast/) cujo host é o próprio Werner.

[![werner6](/images/posts/werner6.png)](/images/posts/werner6.png)

Gostei desta timeline que ele montou, mostrando a evolução da tecnologia nas últimas décadas e como foi preciso que nós devs evoluíssemos para acompanhar as novidades.

[![werner7](/images/posts/werner7.png)](/images/posts/werner7.png)

E ele termina (pelo menos nas minhas anotações) com essa visão  bem inspiradora sobre o que ele considera que são as características dos developers do futuro. Dado o atual contexto de IA e LLMs, onde a escrita do código deixou de ser um diferencial, estes conhecimentos são cruciais para continuarmos relevantes. Esse slide por si só já é assunto para mais um post (talvez eu escreva sobre isso), por isso gostaria de ler opiniões sobre ele. Mas na minha opinião faz total sentido.

Quero terminar agradecendo a equipe do Sebrae por ter organizado mais um evento incrível, em especial o grande [Alexandre Souza](https://www.linkedin.com/in/alexsouzanet/) que faz um trabalho crucial para o ecossistema de startups brasileiro. É incrível poder contar com palestras inspiradoras como esta foi para mim. 
