---
title: Tipos de Plataforma e como começar
date: 2026-07-04T08:00:43-03:00
draft: false
tags:
  - platform-engineering

---
Terminei o [post anterior](https://eltonminetto.dev/post/2026-07-01-platform-engineering-p1/) prometendo responder à pergunta “como começar”, mas quero dar um passo atrás e trazer outra definição.

## Tipos de plataforma

Do meu ponto de vista, divido as plataformas em duas categorias, de acordo com as abstrações que fornecem para os times de produto:

- **Abstração de funcionalidade**: imagine uma aplicação composta por vários microsserviços, em que cada um deles necessita de autenticação e autorização. Se cada serviço desenvolvesse sua própria solução para o problema, teríamos repetição de código e de esforço, além de padrões diferentes e incompatíveis. Desta forma, faz sentido criarmos uma “plataforma de identidade” (ou outro nome semelhante) que forneça essa funcionalidade a toda a empresa. O time de plataforma de identidade torna-se responsável pela manutenção e evolução, entregando APIs confiáveis e sólidas para que os demais times mantenham o foco em suas regras de negócio. 
- **Abstração de infraestrutura**: neste caso, o time de plataforma cria abstrações para facilitar o uso de recursos de infraestrutura, como bancos de dados, redes, cache, etc. Um exemplo simples poderia ser: ao fazer commit de um determinado arquivo `.yaml`, a pipeline vai criar um bucket no AWS S3, com todas as configurações de segurança e de backup, de forma automática. Desta forma, toda a complexidade por trás do fornecedor ou dos fornecedores de nuvem fica abstraída dos times de produto.

Estas definições devem auxiliar na compreensão deste e dos próximos textos.

Agora vou tentar cumprir minha promessa, sugerindo um pequeno “framework” para começar a construir sua plataforma.

## Como começar

Como mencionei anteriormente, um ponto importante na engenharia de plataforma é a abordagem de produto. Então não deve ser uma surpresa que este tópico fale bastante sobre isso.

Para ilustrar a ideia:

[![framework-plataforma](/images/posts/framework-plataforma.png)](/images/posts/framework-plataforma.png)

Para exemplificar:

- **Identifique a dor**: os times relatam que o deploy de novas aplicações no ambiente de produção demora muito, o que reduz o prazo de entrega e a resolução de incidentes.
- **Colete métricas**: ao coletar as métricas da esteira de CI/CD, percebe-se que o tempo para o *build* de uma nova versão é de 30 minutos e o tempo para o *deploy* é mais 15 minutos, totalizando 45 minutos entre o merge do *Pull Request* e a nova versão estar disponível para os usuários.
- **Defina o objetivo**: em conversa com os times de produto e plataforma, define-se que o objetivo é reduzir em no mínimo 50% o tempo do processo.
- **Implemente a melhoria**: o time de plataforma investiga o processo e descobre que o maior tempo ocorre no download de dependências, e implementa um cache. Além disso, aumenta a configuração das máquinas/pods do Kubernetes que executam a esteira de CI/CD, adicionando mais memória e CPU.
- **Compare com o objetivo**: ao final da melhoria implementada, novas métricas são coletadas e o processo leva, no todo, 20 minutos. Isso está abaixo do objetivo, que era de 22,5 minutos.
- **Decida**: Ao comparar o novo tempo com o objetivo, os times podem decidir considerar a interação um sucesso e passar para a próxima dor, ou continuar otimizando o processo.

Novamente, é um fluxo muito parecido com a rotina de um time de produto; a única diferença é que os clientes são outros desenvolvedores/SREs. 

Ao executar esse ciclo continuamente, é possível criar novas melhorias para os times sem recorrer a *overengineering*. Claro que ainda é importante o uso de outras práticas de produto, como a definição de um *roadmap*, mas ciclos curtos de entrega geram recompensas e aprendizados para todos os times envolvidos.

## Próximos passos

Nos próximos textos desta série, quero falar sobre os perfis e papéis dos times de plataforma e de ferramentas, e também sobre o impacto que a IA vem causando nesta área. Se quiser sugerir outros tópicos ou contribuir para a discussão, use os comentários para colaborar.