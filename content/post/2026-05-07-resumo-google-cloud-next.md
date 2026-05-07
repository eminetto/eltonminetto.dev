---
title: Resumo Google Cloud Next 2026
date: 2026-05-07T07:00:43-03:00
draft: false
---

De 22 a 24 de Abril aconteceu em Las Vegas o Google Cloud Next. Trata-se do evento onde o Google apresenta as novidades sobre o GCP e tecnologias relacionadas. Ele é um evento-irmão do Google IO, que acontece na Califórnia em Maio. Eu tive a oportunidade de participar do IO nos dois últimos anos, graças ao programa Google Developer Expert (GDE)  e este ano nos levaram para o Next.

> Uma curiosidade: no momento são 1367 pessoas com o título de GDE no mundo. Destas só 17 são GDEs de Go, sendo 3 brasileiros: [Tiago Temporin](https://www.linkedin.com/in/tiago-temporin/), [Alexandre Cabral](https://www.linkedin.com/in/alexandre-cabral-bedeschi/) e eu.

O Next é uma grande feira de empresas que têm relação com o GCP, incluindo “concorrentes” como o Github Copilot e a Anthropic, bem como algumas empresas que são conhecidas nossas aqui no Brasil (Databricks, Clickhouse, ZScaler, Oracle, IBM). Só a AWS não está lá, por motivos óbvios :). Além da feira, o evento tem mais dois andares de palestras acontecendo o tempo todo. E algumas empresas fazem palestras em seu próprio espaço na feira, como era o caso da Atlassian, Anthropic, etc. 

## GDE Summit

Na segunda e terça tivemos um evento privado para os GDEs, onde assistimos algumas palestras interessantes, bem como tivemos a oportunidade de conversar com os times de produto do Google sobre as áreas que somos experts. Participei de uma conversa com o [Marc Dougherty](https://www.linkedin.com/in/doughertymarc/), do time do Go. Pudemos levar sugestões, fazer perguntas sobre o futuro da linguagem, etc. Dentre as sugestões, eu reforcei o que eu já havia comentado [neste post](https://eltonminetto.dev/post/2025-06-19-go-more-opinated/) e que seria interessante existir uma Skill oficial de Go para os agentes de IA (mais sobre isso nos próximos parágrafos) gerarem código mais idiomático. Se essas sugestões terão algum efeito só o tempo vai dizer ;) 

Dentre os insights desse primeiro evento eu salvei alguns:

[![gnext1](/images/posts/GoogleCloudNext/1_skills.jpg)](/images/posts/GoogleCloudNext/1_skills.jpg)


O importante nesta imagem é o link [https://agentskills.io/home](https://agentskills.io/home)

[![gnext2](/images/posts/GoogleCloudNext/2_skills.jpg)](/images/posts/GoogleCloudNext/2_skills.jpg)

Anunciaram o lançamento das skills oficiais dos produtos do GCP: [https://github.com/google/skills](https://github.com/google/skills) que podem ser bem úteis para conectar agentes às ferramentas deles. Outro assunto relacionado foi o anúncio de que todos os produtos do GCP agora tem suporte nativo a MCP. 

[![gnext3](/images/posts/GoogleCloudNext/3_skills.jpg)](/images/posts/GoogleCloudNext/3_skills.jpg)

Gostei dessa reflexão sobre as perguntas que os Agentes trazem pro nosso dia a dia, bem como o que muda e o que não muda.

[![gnext4](/images/posts/GoogleCloudNext/4_sobre_agents.jpg)](/images/posts/GoogleCloudNext/4_sobre_agents.jpg)

[![gnext5](/images/posts/GoogleCloudNext/5_oque_nao_muda.jpg)](/images/posts/GoogleCloudNext/5_oque_nao_muda.jpg)

[![gnext6](/images/posts/GoogleCloudNext/6_oq_muda.jpg)](/images/posts/GoogleCloudNext/6_oq_muda.jpg)

## Cloud Next

Na quarta começou o Next, com esse [Keynote](https://www.youtube.com/watch?v=11PBno-cJ1g). Recomendo bastante pois fala sobre a parte de negócios do GCP e das novidades e parcerias (como a Apple, que teve destaque na apresentação). Desta talk eu salvei alguns itens:

[![gnext7](/images/posts/GoogleCloudNext/7_geap.jpg)](/images/posts/GoogleCloudNext/7_geap.jpg)

Esse foi o grande lançamento do evento. Eles renomearam um produto chamado VertexAI para *Gemini Enterprise Agent Platform* (o Google provando que ainda precisam melhorar no quesito "dar nomes para produtos"). Eles querem se tornar a melhor plataforma para hospedagem e ciclo de vida de AI Agents, dividindo todos os produtos em 4 pilares:

- Build
- Scale
- Govern 
- Optimize

Nas próximas duas imagens é possível ver o tamanho da plataforma.

[![gnext8](/images/posts/GoogleCloudNext/8_geap.jpg)](/images/posts/GoogleCloudNext/8_geap.jpg)

[![gnext9](/images/posts/GoogleCloudNext/9_geap.jpg)](/images/posts/GoogleCloudNext/9_geap.jpg)


No pilar Build, o grande destaque foi o [ADK](https://adk.dev/)

[![gnext10](/images/posts/GoogleCloudNext/9_adk.jpg)](/images/posts/GoogleCloudNext/9_adk.jpg)

Várias palestras durante o evento mostraram como criar agentes com o ADK. Eu assisti duas talks sobre ADK + Go, mas existe suporte a outras linguagens. 

Na quarta-feira eles apresentaram a [Developer Keynote](https://www.youtube.com/watch?v=A01DQ8_xy7Q) onde mostraram demo de cada um dos pilares e eu recomendo fortemente para entender como tudo se integra. Eles pensaram em todo o ciclo de vida de um Agent, desde o build até coisas como segurança e observabilidade. 

Outro ponto interessante do evento é que tudo que foi apresentado nos Keynotes é possível de ser replicado via Google Codelabs, incluindo créditos do GCP para que você possa fazer o teste na infra deles.

## Meus insights

Minha principal conclusão é que os agentes são realmente a grande aposta do mercado, pois uma gigante como o Google não iria investir tanto em algo que não fosse um retorno real. Outro ponto que repetiram bastante é que o momento de experimentação passou, agora é hora de vermos a IA (LLMs, Agents) com seriedade e responsabilidade, olhando para custos (tiveram várias talks sobre FinOps e empresas mostrando ferramentas), performance, escalabilidade, etc. 

Outro assunto que me interessou bastante foi o lançamento do [Gemma 4](https://deepmind.google/models/gemma/gemma-4/) . Trata-se do modelo open source do Google, que permite criarmos um “Gemini próprio”. Eu acredito que esse seja um conceito que vai crescer bastante nos próximos meses, com empresas rodando modelos nos seus clusters, ganhando mais controle sobre custos, performance, otimização, etc. É um assunto que eu quero me aprofundar nos próximos meses. 

Um ponto a frisar é que mesmo se o Google não for seu fornecedor principal de cloud, vários dos conhecimentos podem ser aplicados, especialmente coisas como os conceitos de Agent Registry, Agent Gateway, Observabilidade (em uma palestra foi apresentado os conceitos de Investigation Agent e Optimize Agent que fazem investigações de incidentes e otimização de performance e custos).  Recomendo assistir o Developer Keynote para assimilar estes conceitos. 

[![gnext11](/images/posts/GoogleCloudNext/10_optimize.jpg)](/images/posts/GoogleCloudNext/10_optimize.jpg)

Se tiver alguma pergunta sobre algum assunto que deixei de fora pode me chamar nos comentários deste post ou no Linkedin que posso aprofundar em algum tópico.

P.S.: 

- Eu participei de um [episódio](https://open.spotify.com/episode/6pSaN5UxxhVHLWi09fhCv7?go=1&sp_cid=9912ddf5ff55e9fa4cdccd4a72b13bf4&nd=1&dlsi=27556b94443e4a90) do podcast da Codecon falando sobre o que eu vi no evento. 
- Las Vegas é bem legal. 
- Se quiser dicas de como ganhar dinheiro nas máquinas do cassino, pergunte para o [Mario Souto](https://www.youtube.com/@DevSoutinho) que ele tem os truques :D
