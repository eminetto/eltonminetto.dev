---
title: Como eu uso IA
date: 2025-11-27T08:00:00-03:00
draft: false
---
Nestes quase 30 anos de carreira vi muitas tecnologias e ferramentas surgirem (e morrerem) e, olhando em retrospecto, eu sempre tive duas premissas sobre como adotá-las:

- A ferramenta/tecnologia deve me tornar melhor
- Eu não devo depender dela para fazer meu trabalho

Dito isso, eu considero toda a “revolução” (na verdade acho que “evolução” seria uma palavra mais justa neste contexto) das LLMs (e todas as tecnologias relacionadas) como mais uma destas ferramentas. Neste post quero descrever como eu venho tentando aplicar estas mesmas premissas nos dias de hoje.

## A ferramenta/tecnologia deve me tornar melhor

Aqui é onde eu sinto a maior empolgação quanto ao que temos disponível hoje em dia. Usar algo como o Copilot (para exemplificar uma solução) para me explicar o código de um projeto legado, ou que eu não conheço, ou mesmo uma linguagem que eu não tenho experiência, é algo que acelera muito o meu aprendizado.

Mas a ferramenta que eu mais gosto, e acho que deveria ter mais destaque no dia a dia das pessoas, é o [Google NotebookLM](https://notebooklm.google.com/). Eu tenho usado ele para aprender conceitos de maneira mais rápida. Só pra citar alguns exemplos onde usei recentemente:

[![notebooklm.png](/images/posts/notebooklm.png)](/images/posts/notebooklm.png)


Coletei várias fontes como links, documentos, videos e pedi para o NotebookLM criar mapas mentais, audios e videos me explicando os conceitos básicos. Fiz perguntas mais avançadas para entender os detalhes.

O resultado é que eu aprendi novos assuntos de uma maneira muito mais rápida do que faria antes, e isso me torna um melhor como profissional e pessoa.

Meu colega, o [Diego Sana](https://www.linkedin.com/in/diegosana/), me indicou uma [abordagem bem interessante](https://simonwillison.net/2025/Nov/6/async-code-research/) de como usar Agentes para criar pequenos projetos de pesquisa, para aprendizado rápido. Gostei bastante da abordagem e quero dedicar um tempo para testar algo assim.

## Eu não devo depender dela para fazer meu trabalho

Aqui é onde talvez este texto gere um pouco de polêmica. Sempre que eu preciso desenvolver algum novo projeto eu começo tomando todas as decisões de arquitetura e design do código e só depois eu recorro a ajuda de algum Copilot (ou outro concorrente). Minha linguagem principal nos últimos anos tem sido Go, por isso começo definindo a estrutura de pacotes, structs e  interfaces de acordo com boas práticas da linguagem/comunidade (expliquei mais sobre estas boas práticas neste [video](https://www.youtube.com/watch?v=KrgIJdrh46A&t=1s)). Com isso organizado eu começo a usar a LLM/Agente para me ajudar, usando prompts para gerar pequenas funções, as quais eu reviso todo o código antes de fazer o commit. Também peço para criar testes unitários e de integração, gerar documentação e outras tarefas repetitivas e que pouco acrescentam no meu conhecimento. Isso faz com que a LLM “alucine” pouco pois o contexto é menor e também me ajuda a ter o controle da qualidade e conhecimento do projeto, o que vai ser muito útil quando eu precisar fazer manutenções e evoluções no código.

Tem momentos que eu abro mão deste controle e uso algum Agente para criar todo o código para mim, mas geralmente este cenário é o de algum script que vou executar poucas vezes, ou mesmo uma prova de conceito que vai ser descartada em detrimento de um projeto mais estruturado.

E usar o Copilot para gerar a documentação de pull requests é uma das soluções que acho mais úteis no dia a dia.

Eu acredito que esta abordagem me torna mais produtivo sem que eu perca a minha independência da ferramenta.

Para finalizar este texto quero acrescentar alguns pontos, adiantando alguns possíveis comentários :)

- Isso é como eu uso em Novembro de 2025, é bem provável que eu mude algo no futuro.
- Isso é a forma como EU uso, não quer dizer que estou 100% correto, ou que alguém está errado :) Cada pessoa tem sua abordagem para aprender e usar novas tecnologias.
- Não sou um velho reclamando das novas tecnologias, também estou empolgado com todas as possibilidades e novidades surgindo todos os dias 🙂

E você? Como tem usado estas novas soluções?
