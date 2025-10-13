---
title: "Developer productivity for fun and profit - Parte 2"
date: 2023-08-01T08:30:43-03:00
draft: false
---

Esta é a segunda parte de uma série de posts que escrevi sobre produtividade. Na [primeira parte](https://eltonminetto.dev/post/2023-01-25-developer-productivity-fun-profit-p1/) falei sobre como eu acredito que a pessoa desenvolvedora pode melhorar sua produtividade. Neste texto vou citar algumas formas com que a empresa/time pode melhorar o dia a dia das pessoas desenvolvedoras.

## Faça Onboarding

Iniciar em uma nova empresa, time ou projeto é algo que pode ser estressante por si só. A ideia é que a pessoa comece a ser produtiva o quanto antes, o que acaba gerando uma boa dose de pressão. Para melhorar isso é importante ter um processo de *onboarding* bem estruturado. Uma forma que eu testei diversas vezes foi:
- nos primeiros dias a pessoa trabalha em *pair programming* com o time, a cada dia com uma pessoa diferente. Desta forma ela vai aprendendo os detalhes do projeto e começa a ter entrosamento com o time.
- na segunda semana (ou antes, dependendo do tamanho do time) a pessoa recebe uma tarefa escolhida com atenção, para que ela possa fazer todo o ciclo de desenvolvimento (codificação, testes, deploy em ambiente de homologação e de produção) com autonomia. 

Existem várias formas de se fazer esse processo de onboarding mas o mais importante é que o time se preocupe em defini-lo com atenção, para que as novas pessoas se tornem produtivas o quanto antes.

## Crie uma cultura de documentação

É muito frustrante quando estamos desenvolvendo alguma feature e ficamos bloqueados esperando a resposta para alguma dúvida ou esclarecimento que está apenas na cabeça de alguma pessoa. Para resolver isso é importante que seja criada uma cultura de documentação no time. Design docs, RFCs, ADRs, videos, existem diversas formas de se realizar isso. Outro ponto importante é que todas essas documentações estejam estruturadas e sejam de fácil pesquisa e consulta. Ferramentas como Confluence, Wikis do Github/Gitlab, Notion, são importantes desde que bem usadas.

## Defina padrões

Outro ponto importante para acelerar o desenvolvimento é ter padrões bem definidos. Isso vai ajudar na escrita do código, no code review, na manutenção futura e evitar que seja perdido tempo em discussões sobre "tabs ou espaços?" e tópicos similares.

A maioria das linguagens possui padrões de *coding style* que os times podem adotar. Caso não exista é possível documentar um padrão e adotar entre o time. E não para nisso, pois podemos definir padrões em relação a criação de APIs (Rest x RPC? URls no singular ou plural? etc), documentação (como citado acima), [microsserviços](https://microservices.io/patterns/index.html), etc.

## Diminua a carga cognitiva

O desenvolvimento de software por si só é algo complexo. Além disso é necessário que a pessoa entenda os detalhes do negócio para o qual está escrevendo soluções. Qualquer complexidade além destas podem diminuir a produtividade e deveriam ser alvo de melhorias. Por exemplo:

- Tornar infra e processos de build/deploy transparentes para os devs
- Adoção de bibliotecas que implementem funcionalidades como log, autenticação, autorização, cache, observabilidade, etc, que são comuns a um grande número de cenários
- Controle de qualidade automatizado com ferramentas como Sonar ou Codeclimate
- Criação de novos projetos usando templates
- Coleta de métricas de produtividade
- Otimização do tempo de build e deploy das aplicações
- Facilidade na criação de ambientes como local, QA, etc
- etc


## Crie/use um Internal Development Portal

A ideia é ter um ponto central onde as pessoas podem encontrar os padrões, documentações, projetos, etc. Pode ser feito com uma ferramenta especializada como o [Backstage](https://backstage.io/), Confluence, Github, Google Docs ou alguma implementação interna. A ferramenta não é o mais importante aqui e sim ter uma forma fácil de se encontrar o que está sendo necessário para que a pessoa seja mais produtiva.

## Crie templates úteis

Nada mais frustrante do que entregar uma página em branco e pedir para a pessoa criar um documento complexo ou uma nova feature. É fácil ficar muito tempo olhando para a página e pensando "por onde começo?". Para evitar isso é importante criarmos templates para:

- Documentos como design docs, ADRs, RFCs, etc
- Projetos. É possível fazer isso com [templates de repositórios do Github](https://docs.github.com/en/repositories/creating-and-managing-repositories/creating-a-template-repository), com o Backstage ou com alguma solução interna
- Stories, tasks em ferramentas como Jira ou [Github](https://github.blog/2016-02-17-issue-and-pull-request-templates/)
- [Pull requests](https://github.blog/2016-02-17-issue-and-pull-request-templates/)
- Commits. Para isso gosto bastante do padrão [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) e [templates de commit](https://gist.github.com/lisawolderiksen/a7b99d94c92c6671181611be1641c733)

## Crie processos para incidentes

Uma coisa é certa: vai acontecer algum incidente em produção. Algum cenário que não tinha sido mapeado vai ocorrer, um banco de dados vai ficar sobrecarregado, o fornecedor de nuvem vai ter algum problema, etc. Nestes momentos é importante ter processos bem definidos para guiar as ações de mitigação do problema, correção e documentação do que aconteceu para que não se repita, os famosos [post mortem](https://www.itsmnapratica.com.br/como-fazer-um-post-mortem/). 

Apesar da ideia é que incidentes sejam eventos raros é importante considerá-los como algo que pode afetar a produtividade das equipes. Se o time perde muito tempo para resolver algum problema em produção e não aprende com as ocorrências a tendência é que elas se repitam e consumam ainda mais tempo.

## Crie uma cultura de qualidade

Essa dica se liga diretamente a anterior. Para evitar incidentes, para evitar que o código se torne complexo e difícil de manter é importante que as equipes tenham uma cultura de escrever código com qualidade. É muito frustrante e consome muito tempo alterar código complexo e de baixa qualidade, além de aumentar a probabilidade de erros e incidentes.

Existe um paper bem interessante publicado pelo Google que aponta como a qualidade influencia diretamente na produtividade dos times: [What Improves Developer Productivity at Google? Code Quality](https://research.google/pubs/pub51783/) e recomendo a leitura.

# Conclusões

Estas são apenas algumas dicas que tentei elencar aqui, mas a lista não é exaustiva e gostaria muito de ler suas sugestões nos comentários do texto.
