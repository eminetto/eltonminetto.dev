---
title: "CUPID x SOLID"
date: 2022-11-04T13:00:19-03:00
draft: false
---
Se você desenvolve software profissionalmente é bem provável que já ouviu falar sobre os princípios SOLID pois eles se tornaram praticamente um padrão no mercado. Se não está famialirizado com o termo recomendo a leitura deste [post](https://blog.betrybe.com/linguagem-de-programacao/solid-cinco-principios-poo/). 

Agora que estamos todos na mesma página, vamos continuar...

Como diria o grande Nelson Rodrigues:

> Toda unanimidade é burra

Pensando nisso, quero trazer outro acrônimo interessante, o CUPID. Cada uma das letras significa uma propriedade que um determinado software deveria ter. Segundo o [post original](https://dannorth.net/2022/02/10/cupid-for-joyful-coding/) que trouxe essa definição, existe uma diferença entre "propriedade" e "princípio":

> Princípios são como regras: ou você está em conformidade ou não.
> Propriedades são qualidades ou características do código em vez de regras a serem seguidas. As propriedades definem um objetivo ou alvo central para o qual se mover. Seu código está apenas mais perto ou mais longe do centro, e sempre há uma direção clara de viagem.

Assim como SOLID, CUPID é um acrônimo e cada letra possui um significado, uma propriedade:

- **Composable** (Combinável): joga bem com os outros
- **Unix philosophy** (Filosofia Unix): faz uma coisa bem
- **Predictable** (Previsível) : faz o que você espera
- **Idiomatic** (Idiomático): parece natural
- **Domain-based** (Baseado no domínio): o domínio da solução modela o domínio do problema em linguagem e estrutura

Todas essas propriedades do CUPID foram pensadas para serem inter-relacionadas, então é provável que qualquer alteração feita para melhorar uma tenha um efeito positivo em algumas das outras.

Vamos mergulhar em cada um dos itens. 

## Composable

Funções e APIs simples, compostas de funcionalidades bem específicas são mais fáceis de serem usadas em conjunto com outras partes do sistema. Para atingir isso algumas boas práticas podem ajudar, como possuir poucas dependências, bons nomes de variáveis e funções.

## Unix philosophy

A filosofia Unix dita a criação de componentes que fazem apenas uma coisa da melhor maneira possível, e que trabalham muito bem com outras (assim como o conceito de `Composability` descrito acima). Por exemplo, o comando `ls` lista arquivos e diretórios, mas não sabe nada sobre os conteúdo deles. Existem outros comandos que fazem isso, como o `stat` ou o `cat`. 

Ao mesmo tempo que cada comando do Unix faz apenas uma coisa bem feita, eles se comunicam através do conceito de `pipes`, que anexam a saída de um comando com a entrada de outro. Desta forma podemos criar uma `pipeline` que busca, transforma, filtra, etc. Exemplo:

```bash
eltonminetto.net on master [?] 
❯ ls -lha
total 208
drwxr-xr-x@ 20 eminetto  staff   640B 27 Ago 22:29 .
drwxr-xr-x  48 eminetto  staff   1,5K 28 Out 01:49 ..
-rw-r--r--@  1 eminetto  staff   8,0K 11 Mai 10:14 .DS_Store
drwxr-xr-x@ 17 eminetto  staff   544B  4 Nov 08:14 .git
-rw-r--r--@  1 eminetto  staff   157B  3 Mai  2018 .gitignore
-rw-r--r--@  1 eminetto  staff     0B  2 Mai  2022 .hugo_build.lock
-rw-r--r--@  1 eminetto  staff   255B 28 Fev  2019 _redirects
drwxr-xr-x@  6 eminetto  staff   192B 19 Jun  2018 ansible
-rw-r--r--   1 eminetto  staff   1,2K 27 Ago 22:29 config.toml
drwxr-xr-x@  6 eminetto  staff   192B 27 Ago 22:29 content
drwxr-xr-x   3 eminetto  staff    96B 27 Ago 22:29 data
-rwxr-xr-x@  1 eminetto  staff   743B  3 Mai  2018 deploy.sh
-rw-r--r--@  1 eminetto  staff   2,9K 17 Jan  2021 keybase.txt
-rw-r--r--@  1 eminetto  staff    63K 25 Jul  2018 map_urls_disqus.csv
-rw-r--r--   1 eminetto  staff   181B  2 Ago 15:54 netlify.toml
drwxr-xr-x@ 72 eminetto  staff   2,3K 20 Out 09:10 public
drwxr-xr-x@  3 eminetto  staff    96B 10 Dez  2018 resources
-rw-r--r--@  1 eminetto  staff   1,2K  3 Mai  2018 s3_website.yml
drwxr-xr-x@ 32 eminetto  staff   1,0K 11 Mai 10:14 static
drwxr-xr-x@ 10 eminetto  staff   320B 27 Ago 22:29 themes

eltonminetto.net on master [?] 
❯ ls -lha | sort | grep .toml | wc -l
       2
```

A princípio parece que esse conceito é muito parecido com o `Single Responsibility Principle (SRP)`, o `S` do `SOLID`. Mas "fazer uma coisa bem feita" possui uma perspectiva "de fora", de quem usa o código. Enquanto isso, o `SRP` possui uma perspectiva "de dentro", pois fala sobre a organização do código. 

Um exemplo bem simplista deste conceito poderia ser o código abaixo:

```go
// GetUser Get an user
func (s *Service) GetUser(id entity.ID) (*entity.User, error) {
	return s.repo.Get(id)
}

// DeleteUser Delete an user
func (s *Service) DeleteUser(id entity.ID) error {
	u, err := s.GetUser(id)
	if u == nil {
		return entity.ErrNotFound
	}
	if err != nil {
		return err
	}
	if len(u.Books) > 0 {
		return entity.ErrCannotBeDeleted
	}
	return s.repo.Delete(id)
}

// UpdateUser Update an user
func (s *Service) UpdateUser(e *entity.User) error {
	u, err := s.GetUser(e.ID)
	if u == nil {
		return entity.ErrNotFound
	}
	err = e.Validate()
	if err != nil {
		return entity.ErrInvalidEntity
	}
	e.UpdatedAt = time.Now()
	return s.repo.Update(e)
}

```

A função `GetUser` tem um propósito bem claro e pode ser reutilizada em diversos outros locais.

Aliás, os conceitos `Composable` e `Unix philosophy` casam muito bem com as ideias da programação funcional. Um bom exemplo é o [pipe operator](https://elixirschool.com/en/lessons/basics/pipe_operator) da linguagem Elixir.

## Predictable

Segundo o autor: 

> O código deve fazer o que parece, de forma consistente e confiável, sem surpresas desagradáveis. Deve ser não apenas possível, mas fácil de confirmar isso. Nesse sentido, a previsibilidade é uma generalização da testabilidade.

A intenção do código deve estar clara e óbvia, desde a sua estrutura e nomenclatura usada. Além disso, o código deve ser "determinístico" e "observável". 

Neste contexto, determinístico se refere ao fato do código ter sempre o mesmo comportamento. O autor usa um exemplo bem interessante: mesmo um algoritmo que gera números aleatórios pode ser determinístico no momento que conseguimos predizer seu comportamento em relação ao consumo de memória, rede e CPU.

Outro ponto importante para conseguirmos um código previsível é usarmos as boas práticas versadas pelos conceitos de "Observabilidade" como: instrumentação, telemetria, monitoramento e alertas. Neste [video](https://www.programaria.org/praticas-monitoracao-observabilidade-servicos/), minha colega de PicPay, a [Damiana Costa](https://www.linkedin.com/in/damianacosta/) faz uma ótima introdução ao assunto.


## Idiomatic

O post que trouxe a definição de CUPID também nos brinda com uma das melhores citações sobre o desenvolvimento de software (tradução minha):

> O maior característa da programação é a empatia; empatia pelos seus usuários; empatia pelo pessoal de suporte; empatia pelos futuros desenvolvedores; qualquer um deles pode ser você no futuro. Escrever “código que humanos possam entender” significa escrever código para outra pessoa.

E é isso que significa "idomático" neste contexto.

Algumas linguagens de programação como Go e Python são bem opinativas quanto ao seus respectivos idiomas. Go, por exemplo, possui em seu toolkit básico uma ferramenta para formatar o código, o `go fmt`. Desta forma todos os códigos são automaticamente formatados para o padrão da linguagem, deixando a leitura muito mais fácil para pessoas com qualquer nível de experiência. 

Outras linguagens como JavaScript e PHP deixam isso em aberto, então é importante que o time escolha um padrão para seguir, crie seus "guidelines" e use ferramentas de `lint` para encorajar a consistência dos códigos. 

## Domain-based

Nós desenvolvemos sofwares para resolverem alguma necessidade de negócio. Qualquer que seja seu propósito é importante que o código siga ao máximo a mesma linguagem do negócio, para diminuir a carga cognitiva e evitar equívocos. Isso é parte do que todo o corpo de conhecimento sobre `Domain-driven design` vem pregando à anos.

As linguagens de programação possuem sua própria nomenclatura e construções como `Hash Maps`, `Linked Lists`, `Tree Sets`, `Database Connections`, etc. Mas algumas linguagens permitem que sejam criados novos tipos de dados, que facilitem a leitura e entendimento de todos que forem dar manutenção aos códigos. Por exemplo, considere a seguinte estrutura em Go:

```go
type Payment struct {
	ID                   int                   
	CustomerID           int                  
	InstallmentID        int               
	InvoiceID            int                   
	Value                float64                     
}
```

E a assinatura da função que faz a criação de um novo `Payment`:

```go
func NewPayment(id, customerID, installmentID, invoiceID int, value float64) *Payment {}
```

O uso desta função seria desta forma: 

```go
p := NewPayment(1, 2, 1, 3, 39.99)
```

A leitura do código acima é muito complexa, pois o leitor depende de alguma funcionalidade de uma IDE para conseguir entender o significado de cada parâmetro. 

Podemos refatorar o código, criando tipos que fazem sentido para o negócio:

```go
type PaymentID int
type CustomerID int
type InstallmentID int
type InvoiceID int

type Payment struct {
	ID            PaymentID
	CustomerID    CustomerID
	InstallmentID InstallmentID
	InvoiceID     InvoiceID
	Value         float64
}

func NewPayment(id PaymentID, customerID CustomerID, installmentID InstallmentID, invoiceID InvoiceID, value float64) *Payment {}
```

E o uso da função agora é bem mais legível:

```go
p := NewPayment(PaymentID(1), CustomerID(2), InstallmentID(1), InvoiceID(3), 39.99)
```


Outro ponto importante que o criador do acrônimo CUPID cita, e que eu concordo 100%, é em relação a estrutura de diretórios do software. Alguns frameworks, como `Laravel` e `Ruby on Rails`, fornecem um "esqueleto de projeto" para acelerar o desenvolvimento. Ao olhar para a estrutura do projeto é fácil identificá-lo como "um projeto Laravel" mas não temos muita informação relacionada ao problema de negócio que ele está resolvendo, qual é o propósito da sua existência. 

No artigo [Screaming Architecture](http://blog.cleancoder.com/uncle-bob/2011/09/30/Screaming-Architecture.html) o Robert C. Martin (Uncle Bob) faz a pergunta (tradução minha): 

> Então, o que a arquitetura do seu aplicativo grita? Quando você observa a estrutura de diretórios de nível superior e os arquivos fonte no pacote de nível mais alto; eles gritam: Sistema de Saúde, ou Sistema de Contabilidade, ou Sistema de Gerenciamento de Estoque? Ou eles gritam: Rails, Spring/Hibernate ou ASP?

## Conclusão

O autor do post original tem alguns pontos contra alguns dos conceitos pregados pelo SOLID e essa insatisfação o fez pensar nessa alternativa que apresentei aqui. Particularmente eu não tenho nada contra SOLID, acho os princípios muito importantes e que já se provaram válidos em vários cenários. Eu vejo os itens apresentados pelo CUPID como um complemento ao SOLID e não algo que o substitua ou invalide. É mais uma arma poderosa que podemos usar no nosso arsenal de desenvolvimento de software.