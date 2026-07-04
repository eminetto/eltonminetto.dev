---
title: Sobre Engenharia de Plataforma
date: 2026-07-01T20:00:43-03:00
draft: false
tags:
  - platform-engineering
---

Nos últimos quatro anos, o assunto “engenharia de plataforma” tem feito parte do meu trabalho e dos meus estudos; por isso, estou começando aqui uma série de textos sobre algumas coisas que aprendi nesse período.

O primeiro ponto a observar é que esse conteúdo não reflete necessariamente as opiniões e decisões tomadas na empresa em que trabalho. Principalmente porque cada empresa tem suas necessidades e características, algo que vou mencionar com frequência nestes textos. 

Mas primeiro....

## O que é engenharia de plataforma?

Vamos dar um passo atrás

Segundo o ótimo livro [Engenharia de Plataforma: Um guia para líderes técnicos, de produtos e de pessoas](https://a.co/d/09WvMdz3), da Camille Fournier e Ian Nowland (vou citar várias vezes esse livro, pois é uma das principais referências no assunto):

> Uma plataforma é uma base de APIs de autoatendimento, ferramentas, serviços, conhecimento e suporte que são organizados como um produto interno atraente. Equipes de aplicativos autônomas podem usar a plataforma para entregar recursos de produtos em um ritmo mais rápido com coordenação reduzida.

Desta forma,

> Engenharia de plataforma é a disciplina de desenvolvimento e operação de plataformas.O objetivo dessa disciplina é _**gerenciar a complexidade geral**_ do sistema com a finalidade de fornecer alavancagem ao negócio. Ela faz isso adotando _**uma abordagem de produto**_ com curadoria para desenvolver plataformas como abstrações baseadas em software que atendem a uma ampla base de desenvolvedores de aplicativos, operando-os como as bases do negócio.

Os destaques no texto são minhas contribuições, pois são dois pontos que considero muito importantes. 

Gosto muito da citação do sociólogo e filósofo francês Edgar Morin:

> A complexidade não se elimina, move-se e a forma muda

O desenvolvimento de software, em geral, bem como os problemas que resolvemos com código, é, por sua natureza, complexo. Um dos papéis da engenharia de plataforma é transferir parte dessa complexidade para longe dos “times de produto”. 

Aqui cabe uma divisão simplória, que vou detalhar melhor em algum dos textos dessa série: 

- “time de plataforma” é o time responsável pelo desenvolvimento e manutenção da plataforma;
- “time de produto” é o time que vai usar a plataforma para construir outros produtos de forma mais simples. 

Existem outras definições melhores, mas, para fins deste texto, vou adotar estas. 

O outro ponto que destaquei na definição de engenharia de plataforma refere-se à “abordagem de produto”. Históricamente, os times desenvolvem scripts e pequenas aplicações para resolver seus problemas do dia a dia, seja para facilitar o build ou o deploy, gerar dados para executar testes, automatizar o setup da máquina, etc. Mas o foco destas pequenas automações sempre foi a própria pessoa ou o próprio time. Quando escalamos isso para uma plataforma, é necessário olhar para a empresa como um todo, para os demais times, para diferentes níveis de conhecimento, etc. Uma armadilha comum em que times de plataforma podem cair é achar que, por serem desenvolvedores/SREs, são o próprio cliente e não criar processos de escuta ativa para entender quais são as dores reais dos demais usuários da plataforma. Por isso é importante ter essa mentalidade de produto desde o começo.

## Por onde começar?

Agora que sabemos os principais conceitos, provavelmente as próximas perguntas são “por onde começar?” e “como começar?”. 
Neste momento vou abordar a primeira pergunta e deixar a segunda para detalhar nos próximos textos.

A resposta mais simples seria: “depende” :) A mais correta seria: “por onde dói mais”. 

Lembra da “abordagem de produto” que comentei no tópico anterior? Esse é um dos momentos em que isso se faz necessário. O primeiro passo é conversar com os clientes da plataforma, os “times de produto”. Perguntar e observar onde, no [ciclo de vida de desenvolvimento de software (SDLC)](https://www.ibm.com/br-pt/think/topics/sdlc), os times têm os maiores atritos e começar a resolvê-los. 

Se o time demora dias para fazer o setup inicial de um novo projeto, uma primeira entrega seria uma automação que, com alguns cliques, permita criar e instalar um novo repositório na máquina do dev ou em QA em minutos. Isso pode ser feito com uma CLI, um script, uma interface gráfica personalizada ou uma ferramenta como o [Backstage](https://backstage.io/). 

Se o time demora horas para fazer o build e o deploy de uma nova funcionalidade, um esforço para otimizar a esteira de CI/CD vai gerar um grande retorno.

Se o time tem dificuldade em identificar a causa raiz de um incidente em produção, melhorias na stack de observabilidade vão fazer milagres no downtime do sistema.

Apenas para citar alguns exemplos. A ideia é usarmos mais um conceito de produto: o MVP (Minimum Viable Product). No nosso caso, podemos usar a licença poética e chamar de Minimum Viable Platform.

Com estas primeiras entregas, o time de plataforma começa a ganhar reconhecimento perante os demais times e dá o primeiro passo para construir algo cada vez mais completo.

## Próximos passos

Neste texto, tentei apresentar apenas uma breve introdução aos termos e conceitos. Nos próximos posts, devo me aprofundar em outros assuntos relacionados, mas gostaria de sua contribuição, nobre leitor(a). Use o espaço dos comentários ou me mande uma mensagem no [Linkedin](https://www.linkedin.com/in/eminetto/) com sugestões de assuntos ou dúvidas que gostaria de ver abordados nestes textos.

