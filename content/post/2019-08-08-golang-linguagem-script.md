---
title: "Usando Golang como linguagem de script"
subtitle: ""
date: "2019-08-08T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

Dentre as decisões técnicas que fizemos durante o desenvolvimento da [Codenation](https://codenation.dev), uma das mais acertadas foi a escolha de Go como linguagem principal.

<!--more-->

Graças a esta escolha, aliada a adoção da [Clean Architecture](https://eltonminetto.net/en/post/2018-03-05-clean-architecture-using-go/), conseguimos ganhar produtividade ao usar a mesma linguagem para diversas tarefas como:

- nosso servidor de API
- lambda functions
- [migrations](https://eltonminetto.net/en/post/2019-01-23-migracao-de-dados-com-go-e-mongodb/)
- nosso CLI, que executa nas máquinas dos clientes
- chatbot do Slack para automatizar tarefas internas
- e o motivo deste post: scripts de automatização de tarefas

Dando uma olhada rápida no nosso repositório é possível ver o Go espalhado por todas estas finalidades:

[![go_codenation](/images/posts/go_codenation.png)](/images/posts/go_codenation.png)

No diretório `cmd` é possível visualizar alguns dos usos que fazemos do Go como linguagem de script. Alguns deles são executados como parte do workflow de desenvolvimento do time, como o `trello-github` que automatiza tarefas de integração entre estas duas ferramentas. Outros são executados como tarefas agendadas via `crontab`, como o `load-bi`. E outros são executados como parte do workflow do nosso servidor de [CI/CD](https://eltonminetto.net/en/post/2018-08-01-monorepo-drone/), como as `migrations`.

Entre as vantagens do Go neste cenário, em comparação com shell scripts, posso citar:

- é uma linguagem simples e poderosa. Podemos usar features como `go routines` para criarmos scripts performáticos
- podemos reutilizar a mesma camada de negócios usada pelo resto do projeto, graças a Clean Architecture
- por ser uma linguagem que permite a compilação para múltiplas plataformas é possível gerar binários que são facilmente executados em qualquer máquina de desenvolvedor ou servidores. Não ter dependências na execução de uma tarefa de automação facilita muito
- é possível ser usada sem passar pelo processo de compilação, bastando executar com o `go run`. Neste caso ter o executável do Go é um requisito, mas a instalação é simples e pode também ser automatizada.

## Pacotes úteis

Vou deixar aqui uma lista de pacotes que usamos para auxiliar na criação destes pequenos aplicativos:

[**github.com/fatih/color**](https://github.com/fatih/color)

Facilita a criação de mensagens coloridas, o que aumenta a usabilidade. Exemplo:

[![cli_go](/images/posts/cli_go.png)](/images/posts/cli_go.png)

[**github.com/schollz/progressbar**](https://github.com/schollz/progressbar)

Na imagem acima é possível ver este pacote em funcionamento. Com ele é fácil criar barras de progresso, útil em processos que demoram para executar.

[**github.com/jimlawless/whereami**](github.com/jimlawless/whereami)

Este pacote é útil para gerar mensagens de erro, pois ele captura o nome do arquivo, linha, função, etc. Por exemplo:

```
File: whereami_example1.go  Function: main.main Line: 15
```

[**github.com/spf13/cobra**](github.com/spf13/cobra)

O cobra é provavelmente a biblioteca mais usada para o desenvolvimento de aplicações em linha de comando em Go. Segundo a documentação ele é usado em projetos importantes como Kubernetes, Hugo, Docker, entre outros. Com ele é possível criar aplicações profissionais, com processamento de input, opções, documentação. Usamos ele no nosso CLI, como no exemplo:

[![cobra](/images/posts/cobra.png)](/images/posts/cobra.png)

Existem outros pacotes e bibliotecas que podem auxiliar no desenvolvimento de aplicativos de linha de comando, para automatizar diversas tarefas do seu workflow de desenvolvimento e das demais equipes. No projeto [Awesome Go](https://github.com/avelino/awesome-go#command-line) é possível encontrar diversas opções interessantes.

Espero que estas dicas ajudem a inspirar novos usos da linguagem em seus projetos.
