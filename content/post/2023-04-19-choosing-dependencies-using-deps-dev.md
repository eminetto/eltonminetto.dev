---
title: "Escolhendo dependências usando deps.dev"
date: 2023-04-19T08:30:43-03:00
draft: false
tags:
  - go
---

Escolher as dependências de um projeto é algo que algumas vezes menosprezamos, mas que pode ter um impacto muito relevante. A imagem a seguir ilustra bem a ideia:

[![dependencies](/images/posts/dependecies.png)](/images/posts/dependecies.png)

Para facilitar esse processo recentemente o Google lançou um novo projeto, o [deps.dev](https://deps.dev) e seu slogan resume bem o seu objetivo: _Understand your dependencies_. A ferramenta tem suporte a algumas linguagens de programação como JavaScript, Rust, Go, Python e Java.

Para mostrar as vantagens vamos imaginar um cenário: uma equipe está desenvolvendo uma API em Go e precisa escolher uma biblioteca para implementar o conceito de [Circuit Breaker](https://martinfowler.com/bliki/CircuitBreaker.html). Após algumas pesquisas na internet e no excelente site [Awesome Go](https://awesome-go.com/) a lista ficou reduzida as seguintes opções:

- [sony/gobreaker](https://github.com/sony/gobreaker)
- [mercari/go-circuitbreaker](https://github.com/mercari/go-circuitbreaker)
- [rubyist/circuitbreaker](https://github.com/rubyist/circuitbreaker)
- [afex/hystrix-go](https://github.com/afex/hystrix-go)

Vamos pesquisar cada uma delas no deps.dev para começarmos a comparação. Estes são os links das análises das libs:

- [sony/gobreaker](https://deps.dev/go/github.com%2Fsony%2Fgobreaker)
- [mercari/go-circuitbreaker](https://deps.dev/go/github.com%2Fmercari%2Fgo-circuitbreaker)
- [rubyist/circuitbreaker](https://deps.dev/go/github.com%2Frubyist%2Fcircuitbreaker)
- [afex/hystrix-go](https://deps.dev/go/github.com%2Fafex%2Fhystrix-go)

Dentre as informações apresentadas algumas me chamaram atenção. Por exemplo, na análise da `gobreaker`:

- A ferramenta cria um score para a lib, usando critérios como segurança, licença e se ela é ativamente mantida:

[![dependencies_score](/images/posts/dependencies_score.png)](/images/posts/dependencies_score.png)

- Podemos ver quantas dependências a lib tem e em quantos projetos ela é usada, o que pode ser um bom sinal de qualidade e confiança da comunidade:

[![dependencies_dependents](/images/posts/dependencies_dependents.png)](/images/posts/dependencies_dependents.png)

Também é possível visualizar se a lib possui algum aviso de segurança. A lib `mercari/go-circuitbreaker` apresenta risco neste quesito:

[![dependencies_security](/images/posts/dependencies_security.png)](/images/posts/dependencies_security.png)

Com estas informações em mãos o time pode tomar uma decisão mais segura quanto a quais libs podem ser incluídas ao seu projeto.

Outra funcionalidade bem útil é que o deps.dev possui uma [API](https://docs.deps.dev/api/v3alpha/index.html). Com essa API é possível criar uma verificação no serviço de `Continous Integration` do projeto para verificar se existe algum aviso de segurança relacionado as dependências, ou se existe uma versão nova de alguma biblioteca importante.

O deps.dev é um projeto bem útil e que pode ajudar bastante os times na escolha e gerenciamento das dependências de seus projetos.
