---
title: Times de Engenharia de Plataforma
date: 2026-07-11T20:00:43-03:00
draft: false
tags:
  - platform-engineering
---

Dando continuidade à minha [série de posts](https://eltonminetto.dev/tags/platform-engineering/) sobre engenharia de plataforma, neste texto quero falar um pouco sobre minha visão dos times de plataforma.

A primeira questão que geralmente perguntamos é:

> Quando faz sentido montar um time de plataforma?

Li alguns posts no passado que tentavam definir uma regra matemática para responder a esta dúvida:

- Este [post](https://platformengineeringcost.com/team-structure) sugere 1 engenheiro de plataforma para cada 8 a 12 engenheiros de produto, com um time mínimo viável de 2-3 pessoas, subtimes formados a partir de 80 engenheiros e um gerente dedicado necessário a partir de 120 engenheiros.
- Esta [thread](https://www.gartner.com/peer-community/post/how-many-engineers-focus-platform-right-size-platform-engineering-team-at-enterprise-level), do Gartner, sugere algo como 10% da organização de engenharia dedicada à plataforma, podendo cair para 5% se a plataforma for menos complexa.

Particularmente eu gosto de ver de outra forma, mais simples:

> Faz sentido montar um time de plataforma quando o custo por não ter um time é maior do que o custo de tê-lo

Exemplo: se os times de produto gastam X horas por semana para fazer o deploy de aplicações, é possível calcular o valor perdido neste gargalo. Se esse valor for maior do que o custo de manter um time para otimizar este fluxo, me parece começar a fazer sentido criá-lo.

Quanto aos possíveis formatos do time, podemos usar alguns conceitos apresentados no livro [Team Topologies, 2nd Edition: Organizing Business and Technology for Fast Flow of Value](https://a.co/d/0fAB9RJf). Neste livro, os autores definem algumas formas de estruturação de times:

- **Stream-aligned team**. Alinhado a um fluxo de trabalho contínuo — um segmento de negócio, produto, jornada do usuário ou conjunto de features. **É o que eu chamei, de maneira simplificada, nos posts anteriores de “time de produto”.**
- **Enabling team**. Composto por especialistas em uma área específica (ex.: segurança, testes automatizados, observabilidade). Não entrega funcionalidade diretamente — ajuda times stream-aligned a adquirir capacidades que faltam, atuando de forma temporária e consultiva, para depois se afastar quando o time já absorveu o conhecimento. **Este pode ser o formato inicial de um time de plataforma.** 
- **Complicated-subsystem team**. Cuida de uma parte do sistema que exige conhecimento especializado profundo (ex.: um motor de matching, um algoritmo de pricing, um sistema de vídeo). Existe para evitar que todo stream-aligned team precise ter esse conhecimento raro internamente. **Pode ser o time responsável pelo tipo de plataforma que eu chamei de “Abstração de funcionalidade” no [post anterior](https://eltonminetto.dev/post/2026-07-04-platform-engineering-p2/#tipos-de-plataforma)**.
- **Platform team**. Finalmente, este é o time que fornece serviços internos self-service (infraestrutura, ferramentas, APIs internas) que os stream-aligned teams consomem para entregar mais rápido, sem precisar entender toda a complexidade por trás. 

Estes formatos não são “escritos em pedra” e podem ser criados modelos mistos ou diferentes dependendo da empresa, mas geralmente são bons pontos de partida. 

E quanto ao formato do time de plataforma em si? Como ele se parece? 

Em alguns textos do portal [Platform Engineering](https://platformengineering.org/), uma das melhores fontes de conhecimento sobre o assunto, eles sugerem um diagrama: 

[![platform_teams](/images/posts/platform_teams.png)](/images/posts/platform_teams.png)

Novamente, esta estrutura é apenas uma sugestão. Em [seu livro](https://a.co/d/09WvMdz3), Camile Fournier e Ian Nowland apresentam outros papéis e dedicam um capítulo ao tema de perfis e a sugestões sobre como contratá-los. Recomendo a leitura.

Neste post, eu trouxe um overview de alguns conceitos que acho importantes em relação aos times de plataforma, mas, como frisei em diferentes momentos, esse é um assunto bem flexível. Ele pode mudar conforme o momento da empresa e a complexidade envolvida. Por isso, gostaria de ouvir suas opiniões sobre este assunto nos comentários deste post ou no [Linkedin](https://www.linkedin.com/in/eminetto).
