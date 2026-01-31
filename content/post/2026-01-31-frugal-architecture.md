---
title: As leis da arquitetura frugal
date: 2026-01-31T08:00:43-03:00
draft: false
---

Ano passado tive a oportunidade de participar de uma palestra do grande Werner Vogels, experiência que eu descrevi neste [post](https://eltonminetto.dev/post/2025-08-31-werner-vogels/). A palestra foi inspiradora a ponto de gerar assunto para um [podcast](https://open.spotify.com/episode/4UukTc0k0lAxKM0TJah9c1?go=1&sp_cid=9912ddf5ff55e9fa4cdccd4a72b13bf4&utm_source=embed_player_p&utm_medium=desktop) e para este texto que você está lendo neste momento.  

Na palestra o Werner citou algo que ele chamou de “As leis da arquitetura frugal” e apontou para o [site](https://thefrugalarchitect.com/) que ele mantém. Esse é o assunto deste artigo.

Vamos começar pelo princípio. O que significa “frugal”? Uma rápida pesquisa no Google e temos a seguinte definição:

> Frugal é um adjetivo que descreve alguém ou algo moderado, econômico e simples, especialmente no uso de recursos, dinheiro ou alimentação. Pessoa que evita gastos desnecessários, sendo prudente com recursos financeiros e tempo. Sinônimos: poupador, econômico, parco, moderado, sóbrio, simples, contido, regrado. 

Pela descrição já podemos ter um primeiro vislumbre do que isso significa quando aplicado à arquiteturas de software. Werner vai além, detalhando o argumento em uma série de “leis”, divididas em três fases.

## Fase de design
- **Arquitetar é uma série de escolhas (“trade-offs”)**: em arquitetura cada decisão vem com uma escolha: aumentar a resiliência, por exemplo, aumenta o custo. Geralmente custo, resiliência e performance são requisitos que conflitam entre si e é preciso encontrar o balanço entre requisitos técnicos e de negócio, alinhando tolerância a riscos e orçamento.
- **Faça do custo um requisito não-funcional**: ao definir a arquitetura de projetos geralmente lembramos de requisitos como disponibilidade, escalabilidade, segurança, etc. A proposta aqui é que custos seja um destes requisitos a ser sempre adicionado ao projeto. Muitas empresas e projetos falham por não dedicar a atenção necessária para este requisito e a matemática é simples: “se os custos são maiores do que sua receita, seu negócio está em risco”. Então, custos deve ser um requisito a ser considerado desde o início do projeto e não somente depois que alguma crise financeira se apresente.
- **Sistemas que duram tem alinhamento de custos com o negócio**: a durabilidade de um sistema depende de quão bem alinhado seus custos estão com o modelo de negócios. Quando fazendo o design de um sistema é importante entendermos de onde vem o dinheiro do negócio e garantir que a arquitetura “segue o dinheiro”. Ele cita o exemplo de um e-commerce, onde a dimensão é o número de compras e a arquitetura deve comportar o crescimento deste fator sem perder o controle dos custos. 
## Fase de medição
- **Arquiteturas sensíveis ao custo devem implementar controles de custo**: é essencial para uma arquitetura frugal monitorar os custos e também possuir a habilidade de otimizá-los. Uma abordagem é decompor a aplicação em blocos e criar categorias de acordo com sua criticidade. Componentes mais críticos são essenciais e devem ser otimizados independente dos custos, enquanto componentes menos críticos podem ter um custo menor e serem menos escaláveis ou robustos. 
- **Sistemas não observados geram custos desconhecidos**: sem observação cuidadosa os custos reais de um sistema tornam-se invisíveis e podem sair do controle. É importante dar visibilidade real dos custos para todos os envolvidos, desde a direção até os devs que estão desenvolvendo o sistema. Quanto mais visível e de fácil entendimento maior é o impacto na redução e controle dos custos. 
## Fase de observação
- **Otimização de custos é algo incremental**: a busca por eficiência de custos é uma jornada constante: após o deploy da aplicação precisamos revisar as medições para melhorarmos sua otimização. Latências, erros, queries de bancos de dados que vão se tornando lentas com o aumento dos dados, etc. Ferramentas como profiling de linguagens e bancos de dados são cruciais para este entendimento. Pequenas otimizações vão se acumulando com o tempo e reduzindo custos, especialmente em ambientes de escala.
- **O sucesso incontestável leva a suposições**:  times que tiveram sucesso lançando produtos no passado podem se tornar confiantes em suas escolhas e pararem de se questionar se estão usando as melhores ferramentas. Uma stack que pode ter sido um sucesso em um projeto pode se tornar o gargalo em outro. É importante manter a mente aberta em relação a diferentes linguagens, bancos de dados e ferramentas para analisar qual é a melhor alternativa para o projeto em questão. Um [caso recente](https://levelup.gitconnected.com/rust-intern-saved-tiktok-300k-by-rewriting-a-go-service-49387c6b060a) aconteceu no TikTok, onde eles refatoraram um serviço de Go para Rust e reduziram U$300k de custos. Toda a plataforma deles é escrita em Go, mas eles encontraram um cenário onde a linguagem não era a melhor opção para resolver seu problema e tomaram a melhor decisão.

Em um momento onde as empresas, especialmente as startups, mudaram seu foco de “crescimento a todo custo” para “crescimento com eficiência”, dicas como estas são muito úteis para guiar o desenvolvimento de seus projetos.

Para finalizar, uma das melhores frases do artigo original é (tradução minha):

> Lembre-se, a frugalidade consiste em maximizar o valor, não apenas em minimizar os gastos. E para isso, você precisa determinar pelo que está disposto a pagar.

P.S.: escrevendo este artigo percebi que eu não sou “pão-duro”, sou “frugal” :) 