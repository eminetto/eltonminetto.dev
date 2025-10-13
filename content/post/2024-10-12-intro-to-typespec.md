---
title: Introdução ao TypeSpec
date: 2024-10-12T09:00:43-03:00
draft: false
tags:
  - api
---

Vou começar esse post com um pouco de história. Lá pelo começo da década de 2010, o *hype* do momento era o conceito de *APIs* e *API-first*. É algo que parece trivial hoje em dia, mas precisamos lembrar que a tecnologia vigente antes disso era o SOAP e seus arquivos XML gigantes. Então, APIs leves usando JSON e respeitando os conceitos REST, que tinham sido inventados alguns anos antes, eram uma evolução enorme. 

Neste contexto, começou uma “disputa” entre algumas linguagens de especificação de APIs, pois com mais e mais times adotando o conceito era necessário algo que fosse possível definir, documentar e detalhá-las. Dentre os competidores consigo lembrar do [RAML](https://raml.org) e o que eu estava apostando minhas fichas, o [API Blueprint](https://apiblueprint.org), mas o vencedor foi o Swagger, mais tarde renomeado para [OpenAPI](https://www.openapis.org).

Acelera para 2024 e todo mundo está feliz escrevendo e mantendo arquivos *YAML* de centenas, as vezes milhares de linhas. Certo?

[![apidoc1](/images/posts/tracy-morgan-bruce-willis.gif)](/images/posts/tracy-morgan-bruce-willis.gif)

Pelo visto não sou só eu que tenho essa impressão, pois no começo de 2024 a Microsoft lançou um novo projeto Open Source chamado [TypeSpec](https://typespec.io). Trata-se de um formato baseado fortemente no TypeScript, também criado pela Microsoft, que permite a definição de APIs de maneira simplificada. 

Quem acompanha mais de perto a história do padrão OpenAPI deve achar esse movimento bem estranho, pois a Microsoft é uma das [entidades membro](https://www.openapis.org/membership/members) do padrão. Em uma [talk](https://www.youtube.com/watch?v=yfCYrKaojDo) uma das líderes do projeto TypeSpec comentou os motivos da criação do novo padrão:

- O OpenAPI foi percebido pelas equipes da Microsoft como difícil de escrever, revisar e manter.
- Era difícil seguir as diretrizes da API nesse contexto, especialmente ao trabalhar com muitas equipes novas que “conheciam” o OpenAPI.
- O OpenAPI não domina o mundo da Microsoft em termos de linguagens e protocolos de descrição de API, com outros, como o gRPC, sendo suportados.

Neste momento algum dos leitores pode estar se perguntando algo como:

> Não vejo problemas na OpenAPI, afinal eu apenas gero a documentação a partir do meu código.

Realmente, essa é uma abordagem adotada por alguns times, usando ferramentas como o [swaggo](https://github.com/swaggo/swag). Vejo alguns problemas nessa abordagem, como código “poluído” com [comentários e anotações](https://github.com/swaggo/swag?tab=readme-ov-file#how-to-use-it-with-gin) e o fato de que a API acaba sendo muito influenciada pelo estilo de programação do time. 

Mas existe outra abordagem, que na minha opinião é a melhor: a *design-first*. Nela os times primeiro definem o design da API, pensando no seu consumidor, nos recursos que vão ser expostos, na evolução da mesma. Com isso em mente é preciso ter uma forma simples de documentar este design, que depois será implementado e usado pelos clientes. E eu não usaria a palavra “simples” para definir um documento escrito no padrão OpenAPI...

Vamos colocar a mão na massa para testar isso.

O primeiro passo é a instalação do CLI do TypeSpec, usando o comando:

```bash
npm install -g @typespec/compiler
```
	
No próximo passo eu criei um diretório e inicializei um projeto:

```bash
mkdir post-typespec
cd post-typespec/
tsp init
```

No menu de opções eu selecionei `Generic REST API` e aceitei as opções padrão.

A seguinte estrutura foi criada:

```bash
❯ ls -lha
total 32
drwxr-xr-x   6 eminetto  staff   192B 13 Out 09:51 .
drwxr-xr-x  80 eminetto  staff   2,5K 13 Out 09:50 ..
-rw-r--r--   1 eminetto  staff   102B 13 Out 09:51 .gitignore
-rw-r--r--   1 eminetto  staff    79B 13 Out 09:51 main.tsp
-rw-r--r--   1 eminetto  staff   417B 13 Out 09:51 package.json
-rw-r--r--   1 eminetto  staff    31B 13 Out 09:51 tspconfig.yaml
```

No arquivo `package.json` constam as dependências do projeto, que podem ser instaladas com o comando:

```bash
tsp install
```
	
Com as dependências instaladas podemos descrever a nossa API no arquivo `main.tsp`. Neste caso usei o [exemplo](https://typespec.io/openapi) da documentação oficial:

```typescript
import "@typespec/http";

using TypeSpec.Http;

model Pet {
  name: string;
  age: int32;
}

model Store {
  name: string;
  address: Address;
}

model Address {
  street: string;
  city: string;
}

@route("/pets")
interface Pets {
  list(@query filter: string): Pet[];
  create(@body pet: Pet): Pet;
  read(@path id: string): Pet;
}

@route("/stores")
interface Stores {
  list(@query filter: string): Store[];
  read(@path id: string): Store;
}
```

Com essa definição podemos gerar as especificações usando o conceito de `Emitters`. Vou voltar a este assunto em breve, mas por enquanto vamos usá-lo para gerar a especificação OpenAPI. Para isso, no arquivo `tspconfig.yaml` temos o seguinte código gerado:

```yaml
emit:
  - "@typespec/openapi3"
```

Ao usar o comando a seguir temos o documento `yaml` gerado dentro do diretório `tsp-output/@typespec/openapi3/openapi.yaml`:

```bash
tsp compile .
```
	
Também é possível usar o comando a seguir para que a compilação seja feita automaticamente conforme os arquivos dentro do projeto forem alterados:

```bash 
tsp compile . --watch
```

Fazendo uma comparação de tamanho de arquivos, as 31 linhas de TypeSpec tornam-se 125 linhas de OpenAPI no formato YAML!  Os dois arquivos podem ser comparados na [documentação oficial](https://typespec.io/openapi).

Voltando as `Emitters`. Além do [OpenAPI](https://typespec.io/docs/emitters/openapi3/reference) que vimos em funcionamento, existem opções para geração das especificações em [JSON Schema](https://typespec.io/docs/emitters/json-schema/reference) e [Protobuf](https://typespec.io/docs/emitters/protobuf/reference). Mas mais importante, é possível a [criação de novos emitters](https://typespec.io/docs/extending-typespec/emitters) para geração de códigos, SDKs, outros formatos de documentação, etc. Por exemplo, poderíamos ter um Emitter que lê a especificação em TypeSpec e gera configurações para algum API Gateway como o [Kong](https://konghq.com/products/kong-gateway) ou o [Traefik](https://doc.traefik.io/traefik/) (dei esse exemplo pois é algo que eu quero testar).

Um argumento contra a adoção do TypeSpec pode ser a inclusão de um novo componente na stack do time. Outro pode ser a maturidade baixa do projeto, pois tem menos de um ano (como projeto Open Source), apesar da evolução rápida que tem apresentado, bem como o uso dentro da Microsoft. 

São argumentos válidos e que devem ser considerados sempre que uma nova tecnologia é cogitada para um projeto. Meu objetivo com esse post foi apresentar esta nova opção e deixar a sugestão de observarmos ela de perto, pois pode ser de grande valia em projetos grandes. Com certeza eu vou manter ela na minha lista de ferramentas a testar e validar. 

Qual sua opinião sobre o assunto? Adoraria ouvir opiniões, principalmente do `#teamOpenAPI` :)