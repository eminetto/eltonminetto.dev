---
title: "Projetos interessantes usando WebAssembly"
date: 2024-04-06T08:00:43-03:00
draft: false
tags:
  - go
---
Esta é a última parte de uma série de posts que escrevi sobre uma das tecnologias que eu acho mais impactantes dos últimos anos: WebAssembly. No [primeiro texto](https://eltonminetto.dev/post/2023-11-17-webassembly-using-go-code-in-the-browser/) falei sobre como portar código em Go para executá-lo em um navegador web. Na [segunda parte](https://eltonminetto.dev/post/2023-12-11-running-webassembly-in-go/) mostrei como usar código WebAssembly em um projeto Go e neste quero falar sobre alguns projetos bem interessantes que vem fazendo uso desta tecnologia. 

## Kong Gateway

O Kong é um API Gateway usado por empresas ao redor do mundo. Ele sempre contou com o conceito de "plugins" e "filtros" que permitem que o usuário possa criar novas funcionalidades e adicioná-las a rotas e serviços. ~~Apesar do Kong em si ser feito em Go,~~ (o [Bruno Souza](https://github.com/devxbr) apontou meu erro nos comentários) para que os usuários criássem estas extensões era necessário fazer uso da linguagem de script Lua (OBS: é possível criar plugins em Go nas versões abaixo da 3.4, mas diferente de Lua,  ao usar Go a performance é menor pois a lógica é executada em um processo separado). A [partir da versão 3.4](https://konghq.com/blog/product-releases/gateway-3-4-oss) foi adicionada a opção de usarmos WebAssembly para este fim. Isso adiciona mais flexibilidade para os times usando e criando soluções em cima do Gateway. [Neste post](https://konghq.com/blog/product-releases/webassembly-in-kong-gateway-3-4) é possível entender um pouco mais sobre os detalhes e ver como implementar um filtro simples usando o TinyGo. No repositório do projeto é possível encontrar um [template](https://github.com/Kong/proxy-wasm-go-filter-template) e um exemplo de filtro para implementar [rate limit](https://github.com/Kong/proxy-wasm-go-rate-limiting). 

## Spin e SpinKube

Os próximos dois exemplos são projetos open source mantidos por uma empresa chamada [Fermyon](https://www.fermyon.com/about) com contribuições de empresas como Microsoft e SUSE. O primeiro chama-se [Spin](https://www.fermyon.com/spin) e permite usarmos WebAssembly para criarmos aplicações Serverless. O segundo, SpinKube, combina alguns dos tópicos que mais me empolgam atualmente: WebAssembly e Kubernetes Operators :) Segundo o [site oficial](https://www.spinkube.dev/), "Ao executar aplicativos na camada de abstração Wasm, o SpinKube oferece aos desenvolvedores uma maneira mais poderosa, eficiente e escalável de otimizar a entrega de aplicativos no Kubernetes." Falando em empolgação, [este post](https://dev.to/thangchung/spinkube-the-first-look-at-webassemblywasi-application-spinapp-on-kubernetes-36jd) mostra como integrar o SpinKube com o [Dapr](https://dapr.io/), outra tecnologia que estou muito interessado e devo escrever alguns posts breve ;)

## wasmCloud

Outro projeto que se propõe a facilitar o deploy e execução de aplicações WebAssembly é o [wasmCloud](https://wasmcloud.com/): "wasmCloud é uma plataforma de aplicativos universal que ajuda você a construir e executar aplicativos WebAssembly distribuídos globalmente em qualquer nuvem e em qualquer borda.". Me pergunto quanto tempo falta até grandes players como AWS, Google Cloud e Azure comprem ou implementem soluções similares em seus portfolios...

## Tarmac

O Tarmac na verdade é um framework que facilita a criação de aplicações WebAssembly. Segundo o [site oficial](https://tarmac.gitbook.io/tarmac-framework): "Framework para escrever funções, microsserviços ou monolitos com Web Assembly. Tarmac é independente de linguagem e oferece suporte integrado para armazenamentos de chave/valor como BoltDB, Redis e Cassandra, bancos de dados SQL tradicionais como MySQL e Postgres e recursos fundamentais como autenticação mTLS e observabilidade". É um projeto que vale a análise pois pode acelerar a implementação de aplicações que depois podem ser hospedadas em um dos produtos que citei acima.

## Onyx

O Onyx é algo completamente diferente dos exemplos anteriores pois é uma nova linguagem de programação, com foco em WebAssembly. Segundo a [documentação](https://wasmer.io/posts/onyxlang-powered-by-wasmer), "Onyx é uma nova linguagem de programação que apresenta uma sintaxe moderna e expressiva, segurança de tipos rigorosa, tempos de build extremamente rápidos e suporte multiplataforma pronto para uso, graças ao WebAssembly.". Sendo desenvolvida há mais de três anos é uma linguagem completa, com sintaxe similar a Go, algumas características de linguagens funcionais, gerenciador de pacotes e suporte nas principais IDEs. 

## Conclusões

O objetivo deste post era tentar deixá-lo em um nível de empolgação próximo ao meu :) Vejo grande potencial para a tecnologia e acredito que vale o investimento de estudos pois é algo que deve ganhar mais destaque nos próximos anos, especialmente em cenários próximos a infraestrutura e backend. 

Quais suas opiniões sobre WebAssembly? Acha que é mais um hype ou tem potencial para ser "disruptivo"? Conhece mais alguns exemplos de aplicações interessantes? Contribua aqui nos comentários.