---
title: Engenharia de Plataforma e observabilidade
date: 2026-09-15T06:30:43-03:00
draft: false
tags:
  - platform-engineering
---

Este post faz parte da minha [série de textos](https://eltonminetto.dev/tags/platform-engineering/) sobre engenharia de plataforma, mas também é uma continuação de um assunto que me interessa muito: observabilidade.

Algum tempo atrás, [escrevi](https://eltonminetto.dev/post/2025-01-28-o11y-great-architectures/) um post (que se transformou em uma palestra) sobre como a observabilidade é o pilar para grandes arquiteturas de software. Hoje em dia, acredito que esse papel é ainda maior, pois a observabilidade é um alicerce para decisões de negócio (produtividade, custos, performance, aquisição de clientes etc.), muito mais do que apenas “troubleshooting”.Neste post, quero focar em como a engenharia de plataforma se entrelaça com a observabilidade.

No texto sobre [como começar](https://eltonminetto.dev/post/2026-07-04-platform-engineering-p2/#como-come%C3%A7ar) uma plataforma, eu cito que um dos passos importantes é “Colete métricas”, então esse papel já fica bem claro. A plataforma em si é uma grande usuária das features que a observabilidade oferece: métricas de uso e de consumo de recursos, logs de auditoria, traces para identificação de erros e eventos de comportamento dos usuários. Sem essas informações, o time de plataforma está “voando às cegas” e não consegue evoluir seu produto (lembre-se do “mindset” de produto, que é um pré-requisito para o desenvolvimento de plataformas).

Mas o papel fundamental é fazer o que alguns autores chamam de   “shift-down” (em contraste com o “shift-left”, em que o dev instrumenta o próprio código). Os times de plataforma podem auxiliar os times de produto, fornecendo ferramentas como SDKs e coletores automatizados, para que as telemetrias básicas das aplicações sejam coletadas. Existem padrões comuns que a plataforma pode coletar automaticamente:

- RED Metrics - Rate (Taxa), Errors (Erros) e Duration (Duração)
- USE - Usage (Uso), Saturation (Saturação) e Errors
- Four Golden Signals - Latency (Latência), Traffic (Tráfego), Errors e Saturation

Com a plataforma coletando as telemetrias básicas, os times de produto podem focar em instrumentar o que é específico de seus produtos, geralmente métricas e eventos de negócio.

Outro fator importante que os times de plataforma podem impactar é a padronização dos dados de observabilidade. Ao adotar um padrão de mercado como o [OpenTelemetry](https://opentelemetry.io/), a empresa tem benefícios importantes como:

- Todos os times e serviços possuem a mesma informação e formato, o que é crucial no momento de troubleshooting em um incidente;
- Não existe o “vendor lock-in”. Serviços de observabilidade costumam ser grandes consumidores de orçamento, por isso a possibilidade de trocar de vendor de acordo com benefícios de negociação é algo muito útil; 
- Como todos os serviços possuem o mesmo formato de telemetria, é muito mais simples para um agente de IA analisar os dados e encontrar soluções gastando menos tokens em conversões e padronizações.

Em 2026, é preciso uma grande sinergia entre três disciplinas importantes: plataforma, FinOps e IA. A IA pode ser tanto ofensora, ao gerar uma nova carga de telemetrias sem precedentes, quanto uma grande ferramenta, ao identificar padrões e auxiliar na resolução de problemas. Ao aplicar os pontos que citei neste texto, a plataforma gera grandes benefícios para os times de produto. Mas os custos, tanto da IA quanto da observabilidade, podem facilmente sair do controle; portanto, o acompanhamento contínuo dos custos é imperativo. Mas ao alinhar estes times, existe um grande potencial transformador que alavanca o negócio, o que é o benefício final.

