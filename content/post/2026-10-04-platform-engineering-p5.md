---
title: Sobre Engenharia de Plataforma e IA
date: 2026-10-04T09:00:43-03:00
draft: false
tags:
  - platform-engineering
---

Este é mais um capítulo da série de posts que estou escrevendo sobre um dos assuntos que mais me interessaram nos últimos anos, a engenharia de plataforma. Se você perdeu os tópicos anteriores, aí vai um resumo:

- [Sobre Engenharia de Plataforma](https://eltonminetto.dev/post/2026-07-01-platform-engineering-p1/)
- [Tipos de Plataforma e como começar](https://eltonminetto.dev/post/2026-07-04-platform-engineering-p2/)
- [Times de Engenharia de Plataforma](https://eltonminetto.dev/post/2026-07-21-platform-engineering-p3/)
- [Engenharia de Plataforma e observabilidade](https://eltonminetto.dev/post/2026-09-15-platform-engineering-p4/)

Como o título deve ter chamado sua atenção, não é surpresa que, neste texto, vou falar sobre como a IA afeta essa disciplina da tecnologia.

## O impacto da IA na engenharia de plataforma

Para isso, vou começar fazendo algumas citações de um livro recente que recomendo muito, o [Thinking in Platforms: Platform engineering as the operating model for work in the AI era](https://a.co/d/09HxGX3j):

> A IA impacta a engenharia de plataformas em três camadas: como usuária da plataforma, como interface para a plataforma e como capacidade dentro dela.

> [...] acreditamos que, em breve, todos os negócios funcionarão com base em uma plataforma interna de IA e que será tarefa dos engenheiros da plataforma construí-la e mantê-la.

Estes trechos, traduzidos por mim, sintetizam bem a forma como os times de plataforma vão ganhar destaque neste novo cenário. 

A terceira citação que acho relevante sobre o assunto, ainda do mesmo livro, é:

> A IA amplifica o que você tem; não transforma um sistema falho em um sistema vencedor.

Apesar de ter valor para outros contextos além da engenharia de plataforma, simplesmente embutir IA em algo que já apresenta problemas só vai potencializar suas falhas. Os times de plataforma devem usar a IA para acelerar o desenvolvimento e o onboarding dos usuários, além de dar suporte a essa tecnologia aos demais times. Mas sem planejamento adequado, esse uso indiscriminado pode causar problemas diversos, especialmente em segurança, governança e custos. 

## Plataforma como evolução de maturidade

Outra fonte de referência recente é uma pesquisa chamada [State of AI in Platform Engineering, Volume 2](https://weaveintelligence.io/research/state-of-ai-in-platform-engineering-2026), que é a segunda edição de uma pesquisa sobre o assunto plataformas e IA, respondida por 350 profissionais, de perfis diversos, como engenheiros de plataforma, DevOps, SREs, arquitetos, consultores e líderes técnicos, em escala global.

Nesta pesquisa, os autores citam um framework de maturidade, uma escala baseada em quanto trabalho se confia ao agente e em onde o humano fica em relação ao loop:

- **Nível 0**: *Human is the loop*. Sem agentes

- **Nível 1**: *Human in the loop*. IA sugere, humano executa

- **Nível 2**: *Human on the loop*. Agentes abrem PRs em paralelo; humano valida comportamento

- **Nível 3:** *Humans as orchestrators*. Agentes trabalham a partir de sinais; revisão por exceção.

- **Nível 4:** *Fully autonomous*. Sistemas de agentes iniciam e concluem trabalho dentro de *guardrails*.

Os dados da pesquisa sugerem que o que diferencia os níveis é o uso de uma plataforma. A explicação do relatório é que, a partir do Nível 3, exigem-se caminhos governados e executáveis por máquina, um loop de validação que devolve as falhas ao agente, uma identidade própria para cada agente e ambientes isolados. E estas são características clássicas de uma plataforma interna de desenvolvimento (IDP). É a plataforma, tratada como uma evolução do IDP, que transforma esses requisitos em capacidade reutilizável, desloca a revisão humana para as exceções e permite governar a identidade e o custo. Por isso, só quem reconstrói a plataforma sob os agentes avança.

## A evolução do IDP

No tópico acima falei sobre uma evolução do IDP, mas o que seria isso? Eis que surge um novo conceito: a *Agentic Engineering Platform (AEP)*.

Usando a definição apresentada na pesquisa: 

> O AEP é apresentado como a evolução da IDP, não como sua substituição. É o que a IDP se torna quando agentes passam a ser usuários da plataforma, e não ferramentas acopladas a ela

O texto original vai além, detalhando possíveis camadas de um AEP:

- **Tooling:** os cinco planos que os times de plataforma já operam (developer control, CI/CD, recursos, segurança e observabilidade), agora sob a carga de agentes. APIs confiáveis, *rate limits* previsíveis e modos de falha explícitos deixam de ser boas práticas e se tornam pré-requisitos.

- **Path specifications:** caminhos determinísticos, probabilísticos ou híbridos.

- **Agent infrastructure:** *harness* (execução, contexto, capacidade, avaliação), governança (identidade, segurança, observabilidade) e modelos.

Sobre os tipos de *path*, a diferença entre cada um deles é:

- **Determinístico:** governança barata; é o padrão para tudo o que é auditável, irreversível ou regulado.

- **Probabilístico:** a governança migra para a avaliação, e as "evals viram a suíte de testes".

- **Híbrido:** geração probabilística envolta em gates determinísticos, em loop até passar. Custa mais tokens, mas é auditável.

Entender esses tipos de path é importante para o que citei no início do texto. Usar IA de forma indiscriminada, sem validar o que realmente faz sentido, gera custos desnecessários e outros riscos. Perceber que há necessidades da plataforma que podem permanecer determinísticas é importante para evitarmos inserir IA em todas as funcionalidades sem necessidade. 


## Conclusões

Sobre este texto, o que posso afirmar é “estamos todos aprendendo”. Esse é um momento crucial para todos os ramos da tecnologia, e a engenharia de plataforma não fica de fora. Novos paradigmas estão sendo criados e refutados em questão de semanas ou meses. O momento requer que estejamos de “mente aberta” e que observemos os movimentos para nos posicionarmos melhor, como engenheiros de plataforma e mesmo como profissionais. 

Quanto ao assunto desta série, “engenharia de plataforma” é um tópico muito vasto e em constante evolução, por isso nunca foi meu objetivo cobrir tudo nesta coleção de textos. Novos livros e eventos surgem todos os anos, e a IA está acelerando tudo ainda mais. Desta forma, termino aqui o que havia planejado para esta série, mas gostaria de ler suas sugestões de outros tópicos que considere relevantes. Para isso, use os comentários do post ou entre em contato comigo no LinkedIn; terei prazer em conversar sobre o assunto. 
