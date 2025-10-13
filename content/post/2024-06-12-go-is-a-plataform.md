---
title: Go é uma plataforma
date: 2024-06-12T08:00:43-03:00
draft: false
tags:
  - go

---
Em Maio deste ano, graças ao programa Google Developer Experts, tive a oportunidade de participar do Google I/O em Mountain View/Califórnia. Dentre as várias talks que assisti, uma das minhas preferidas foi a **‌ Boost performance of Go applications with profile guided optimization**, que você pode assistir no [Youtube](https://www.youtube.com/watch?v=FwzE5Sdhhdw). 

Mas apesar do Profile Guided Optimization (PGO) ser uma das features mais interessantes da linguagem, o que me chamou mais a atenção foi a primeira parte da palestra, apresentada pelo [Cameron Balahan](https://www.linkedin.com/in/cameronbalahan/), que é Group Product Manager no Google Cloud.

A parte que explodiu minha cabeça foi a afirmação:

[![platform](/images/posts/go_is_a_platform.png)](/images/posts/go_is_a_platform.png)

Ele começa falando sobre o “DevOps Life Cycle”:

[![sldc](/images/posts/sldc.png)](/images/posts/sldc.png)

E passa a destacar as qualidades de Go em três quesitos importantes:

- velocidade de desenvolvimento;
- segurança;
- performance.

[![go_developer_velocity](/images/posts/go_developer_velocity.png)](/images/posts/go_developer_velocity.png)

Meus comentários sobre cada item citado na imagem:

- **Easy concurrency:** esse é um dos grandes apelos da linguagem graças as `goroutines` e `channels`.
- **IDE integrations:** temos grandes IDEs como a Goland da Jetbrains e o plugin para Go do Visual Studio Code, que é mantido pela própria equipe da linguagem.
- **Dependency Management:** a linguagem demorou um pouco para adotar isso, mas ter a gestão de dependências como algo embutido no toolset é muito útil. Basta um `go get` ou `go mod tidy` para termos as dependências do projeto instaladas. 
- **Static Binaries:** isso acelera o deploy de aplicações, pois basta realizar a compilação e temos um executável auto-contido e pronto para executar.
- **Delve Debugger:** ter um debugger poderoso já configurado em todas as IDEs acelera muito o desenvolvimento e manutenção do código.
- **Built-in Test Framework:** ter a biblioteca de testes embutida na standard lib acelera a adoção de técnicas como TDD.
- **Cross-compilation:** acho incrível do meu macOS poder gerar um binário para Windows, Linux e outras plataformas de maneira tão simples. 
- **Built-in Formatting:** Não existe perda de tempo para discussões de como formatar seu código pois o linter já está no toolset da linguagem e todas as IDEs vem configuradas para formatar seu código ao salvá-lo.

[![go_security](/images/posts/go_security.png)](/images/posts/go_security.png)

- **Module Mirror Checksum DB:** esta feature foi [lançada](https://go.dev/blog/module-mirror-launch) em 2019 e ajuda a garantir a autenticidade dos módulos que a aplicação está usando como dependência. 
- **Memory Safe Code:** “Memory Safe é uma propriedade de algumas linguagens de programação que evita que os programadores introduzam certos tipos de bugs relacionados ao uso da memória.” [fonte](https://www.memorysafety.org/docs/memory-safety/)
- **Compatibility Promise:** Isso é algo que torna a escolha de Go muito mais segura, especialmente para empresas, pois evita a necessidade de grandes refatorações futuras, como aconteceu do PHP 4 para o PHP 5, ou do Python 2 para o Python 3, por exemplo. Mais detalhes no [blog da linguagem](https://go.dev/blog/compat).
- **Vulnerability Scanning:** A ferramenta [govulncheck](https://go.dev/doc/security/vuln/) foi uma adição muito importante ao toolset da linguagem.
- **Built-in Fuzz Testing:** [Fuzz testing](https://go.dev/doc/security/fuzz/) é uma técnica avançada de testes que aumenta a superfície de cobertura de testes com valores arbitrários, melhorando a qualidade do código. 
- **SBOM Generation:** SBOM significa *Software Bill of Materials* e apresenta um inventário detalhado de todos os componentes de software e dependências dentro de um projeto. Neste [post](https://earthly.dev/blog/generating-sbom/) podemos ver algumas formas de gerar esse recurso para aplicações.
- **Source Code Analysis:** A maioria das soluções comerciais de análise estática de código possui suporte a Go, como o [Sonar](https://www.sonarsource.com/knowledge/languages/go/), mas também existem projetos open source como o [golangci-lint](https://golangci-lint.run/).

[![go_performance](/images/posts/go_performance.png)](/images/posts/go_performance.png)

- **Rich Standard Library:** se o lema “com as baterias inclusas” não tivesse sido usado pelo Python ele poderia ser perfeitamente adotado pela comunidade Go. A biblioteca nativa da linguagem tem praticamente tudo que o desenvolvimento de software moderno precisa, desde servidor/cliente HTTP, testes, parsers JSON, estruturas de dados, etc.
- **Built-in Profiling:** Entre as funcionalidades da biblioteca nativa da linguagem consta o `pprof` para podermos fazer análise da performance das aplicações. Escrevi sobre isso em outro [post](https://eltonminetto.dev/post/2020-04-08-golang-pprof/).
- **Runtime Tracing:** Recentemente o time da linguagem fez [melhorias](https://go.dev/blog/execution-traces-2024) na feature de tracing, aumentando os detalhes que podemos coletar das aplicações para entender seu comportamento.
- **Self-tuning GC**: Go tem uma das mais modernas implementações de Garbage Collector e mais detalhes podem ser encontrados no [blog da linguagem](https://tip.golang.org/doc/gc-guide).
- **Dynamic Race Detector**: Outra [funcionalidade](https://go.dev/doc/articles/race_detector) embutida na linguagem que ajuda a detectar problemas mais rapidamente, na fase de desenvolvimento e testes. 
- **Profile-guided Optimization**: Uma das [funcionalidades](https://go.dev/doc/pgo) mais recentes da linguagem e que vem causando grande impacto na redução de consumo de recursos. Mais detalhes podem ser vistos na talk que gerou este post, bem como nesta [apresentação](https://www.youtube.com/watch?v=V2LSnbvylz4) da Gophercon Brasil 2024 feita pelo grande [Alex Rios](https://www.linkedin.com/in/the-alex-rios/).

Gostei muito desta forma de apresentar a linguagem pois mostra de uma forma resumida todo o ecossistema que existe ao seu redor e todos os benefícios que recebemos ao adotá-la. 

O que você acha desta visão? Já havia pensado desta forma? 

