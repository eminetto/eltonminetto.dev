---
title: Go deveria ser mais opinativo
date: 2025-06-19T10:00:00-03:00
draft: false
tags:
  - go
---

Uma das vantagens em ser [Google Developer Expert](https://g.dev/eminetto) é as oportunidades incríveis que isso proporciona. Algumas semanas atrás pude conhecer pessoalmente o [Robert Griesemer](https://en.wikipedia.org/wiki/Robert_Griesemer), co-criador de Go, bem como o [Marc Dougherty](https://www.linkedin.com/in/doughertymarc/), Developer Advocate do time de Go no Google. Em um happy hour após o Google IO o Marc perguntou, para mim e outro GDE de Go da Koreia que fui apresentado, quais feedbacks teríamos em relação a linguagem. Minha resposta foi que eu não tinha nenhum feedback específico sobre a linguagem, mas que

> Go deveria ser mais opinativo em relação a estrutura de diretórios dos projetos

Tentei explicar um pouco o que se passava na minha cabeça, mas achei que valia a pena escrever um post para poder expressar melhor o que eu penso sobre isso.

Começando do começo... Em 2025 completo 10 anos escrevendo códigos em Go e uma das coisas que me lembro de quando comecei, é que iniciar na linguagem foi relativamente simples principalmente graças a dois motivos: a simplicidade da linguagem e o fato de só ter uma forma de se fazer as coisas. Go foi a primeira linguagem que tive contato que tinha opiniões fortes sobre várias coisas. Só tem uma forma se se fazer loop, só tem uma forma de se formatar os arquivos (`go fmt`), variáveis de escopo pequeno devem ter nomes pequenos, etc. Isso facilitou muito a leitura de códigos escritos por outras pessoas, o que é crucial para o aprendizado. O código que eu escrevia era muito parecido com o código do Kubernetes! Claro que a complexidade do problema era infinitamente maior, mas a estrutura do código era legível para mim. E no decorrer dos anos vi esse efeito se repetir em várias pessoas que acompanhei iniciando na linguagem, migrando de outros ambientes.

Mas, passada essa empolgação inicial, vem o maior desafio: como adotar Go em um projeto maior do que os usados para o aprendizado? Como estruturar um projeto que vai ser desenvolvido e evoluído por um time? Neste momento as opiniões fortes ficam de lado e cada time/empresa precisa decidir como estruturar seus projetos. Nesta década eu passei por quatro empresas nesse estágio, e em todas foi necessário investir tempo do time para recolher exemplos, ler documentações e livros, para determinar qual é a estrutura que vai ser usada nos projetos. [Este é o documento](https://medium.com/inside-picpay/organizing-projects-and-defining-names-in-go-7f0eab45375d) da empresa onde trabalho atualmente, que participei da construção.

Fazendo uma analogia com o mundo dos games, é como se estivéssemos nos divertindo no mundo controlado e maravilhoso do Super Mario World e fôssemos transportados para o mundo aberto do GTA 6 (sim! estou no hype!). Continua sendo um universo maravilhoso, mas é uma transição muito abrupta. 

É nesse ponto que eu acredito que Go deveria ser mais opinativo. Deveríamos ter templates de projetos mais comuns como CLIs, APIs, microsserviços, etc, que os times poderiam usar como scaffolding de suas aplicações. O toolkit da linguagem já [permite o uso de templates de projetos](https://go.dev/blog/gonew) então seria questão de existirem templates oficiais para facilitar a vida dos times. Ou poderíamos ir além, incluindo o comando no próprio toolkit da linguagem, com algo como um `go new`. 

Na história da linguagem houve algo parecido. Hoje o `go mod` e todo o gerenciamento de dependências é algo que usamos como parte fundamental do nosso dia a dia como devs Go. Mas nem sempre foi assim. Por muito tempo não existia um gerenciador de pacotes oficial da linguagem, e surgiram várias alternativas criadas pela comunidade. Todas funcionavam, mas a fragmentação estava saindo do controle, dificultando a integração de pacotes. Até que o time da linguagem tomou as rédeas da situação e o `go mod` foi criado, pacificando o assunto “gerenciamento de pacotes e dependências”. Acredito que o mesmo possa ser feito em relação a estrutura dos projetos.

Outro perfil que seria beneficiado com uma estrutura de projetos mais opinativa é o formado por times que estão migrando suas aplicações de outras linguagens, especialmente Java e PHP. Nestes ecossistemas existem frameworks que ditam a estrutura dos projetos, como Spring Boot e Laravel. “Por onde eu começo? Como estruturo meu projeto?” são perguntas comuns que eu ouço de times migrando destas linguagens. Ter algo que facilite essa migração seria importante para diminuir a barreira de entrada e aumentar o número de times experimentando Go em produção.

Então, esse é meu maior feedback em relação a Go no momento. O que você acha, nobre leitor(a)? Qual sua opinião sobre o assunto? Adoraria discutir sobre esse assunto nos comentários ou ao vivo, em alguma conferência.

