---
title: Evitando supply chain attacks em Go
date: 2026-04-05T19:00:43-03:00
draft: false
tags:
  - go
---
Se você acompanhou as notícias das últimas semanas (Março/Abril de 2026), deve ter lido sobre dois grandes “supply chain attacks” que ocorreram. Provavelmente você foi afetado de alguma forma pelo problema que ocorreu com os projetos [LiteLLM](https://docs.litellm.ai/blog/security-update-march-2026) e o [Axios](https://www.elastic.co/security-labs/axios-one-rat-to-rule-them-all). 

Vamos dar um passo atrás e ler a definição sobre este tipo de ataque, de acordo com o resumo do Google Gemini:

> Um ataque à cadeia de suprimentos (*supply chain attack*) é um ciberataque que visa uma organização explorando suas relações de confiança com fornecedores terceirizados, parceiros ou provedores de software. Em vez de atacar diretamente um alvo bem defendido, os invasores comprometem um parceiro mais vulnerável para infiltrar a rede do alvo final, frequentemente utilizando atualizações maliciosas ou software comprometido.

Um bom conceito para se ter em mente é que cada dependência externa que você adiciona ao seu projeto aumenta a “superfície de ataque”, a possibilidade de que algum destes componentes, pelos quais você não é o responsável direto, pode ser usado para causar dano à sua empresa/projeto.

Neste post, quero comentar como mitigar esta categoria de ataques com base em alguns conceitos e ferramentas do toolkit de Go.

## Use the ~~force~~ stdlib Luke

Apesar de o slogan “Batteries Included” ser da linguagem Python, ele também se aplica ao Go. A biblioteca padrão (*stdlib*) da linguagem possui tudo de que a grande maioria dos projetos precisa para serem desenvolvidos. Uma pergunta muito comum de quem está iniciando na linguagem, especialmente vindo de outros ambientes, é qual framework ou biblioteca usar para criar uma API, por exemplo. Apesar de existirem projetos maduros como o *Gin*, o *Chi* e o *Fiber*, que tornam a “developer experience” mais produtiva, o custo de segurança e, muitas vezes, de performance me parece não valer a pena. O pacote `http` da *stlib* evoluiu muito nas últimas versões e atualmente não vejo motivos para não usá-lo, especialmente no cenário de ferramentas como Claude e Copilot, que geram código de qualidade. Instrua seu AI Agent a usar sempre a *stdlib* e seu código ficará menos vulnerável aos ataques mencionados anteriormente.   

Outra vantagem de usar a *stdlib* é a compatibilidade com as novas versões da linguagem, característica fundamental do projeto Go. Ao usar algum pacote de terceiros nada garante que uma nova versão do projeto quebre seu código, ou que uma vulnerabilidade demore para ser resolvida e seu projeto fique desprotegido.

Uma exceção a essa regra diz respeito aos testes. Como o código de testes (os arquivos *_test.go* do seu projeto) não é incluído no binário que vai ser executado em produção, eu não sou tão criterioso ao escolher pacotes úteis, como o [testify](https://github.com/stretchr/testify) ou [Testcontainers](https://golang.testcontainers.org). Mesmo assim, recomendo que siga a próxima dica: confie desconfiando.

## Confie, mas não tanto

Se optar por usar dependências externas em seu projeto, a primeira dica é fazer uma pesquisa no site [https://deps.dev](https://deps.dev). Escrevi um [post](https://eltonminetto.dev/post/2023-04-19-choosing-dependencies-using-deps-dev/) dedicado a esse assunto, mas basta acessar o site e pesquisar pela dependência que está pensando em usar, como o [Testcontainers](https://deps.dev/go/github.com%2Ftestcontainers%2Ftestcontainers-go) para ter acesso a informações importantes como “Security Advisories”, licenças e as dependências que o pacote importa. Este último item é importante, pois as vezes estamos usando um pacote sem lembrar que ele importa outros projetos,e alguma parte desta cadeia pode estar comprometida. 

Pesquisar no *deps.dev* é um bom primeiro passo, mas as ferramentas evoluem, assim como suas vulnerabilidades. Para garantir que nada novo cause mal ao seu projeto, é importante automatizar este processo e, para isso, devemos usar mais uma ferramenta importante do toolkit de Go, o *govulncheck*. Neste [post](https://go.dev/doc/tutorial/govulncheck), no site oficial da linguagem, é possível encontrar um tutorial sobre como usá-lo. Lembre-se de adicionar o *govulncheck* como um passo da sua esteira de CI/CD para que novas vulnerabilidades quebrem o processo de build assim que forem detectadas. 

Quem trabalha com segurança sabe que é uma eterna briga de “gato e rato” com ataques e atacantes, e que é impossível atingir o “nirvana” de estar 100% seguro. Mas seguir dicas como as que citei aqui pode nos ajudar bastante e deve fazer parte do dia a dia de qualquer time de desenvolvimento.