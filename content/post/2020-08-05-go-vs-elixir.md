---
title: "Go vs Elixir, primeiras impressões"
subtitle: ""
date: "2020-08-05T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

Antes de tudo uma explicação: estou escrevendo este post baseado nas minhas impressões depois de aproximadamente um mês lendo bastante sobre Elixir, vendo linhas e linhas de código e finalizando algumas tarefas com a linguagem. Por isso já deixo aqui minhas desculpas por erros grosseiros que eu possa cometer quando estiver escrevendo sobre a linguagem.

Porque escrever este post agora, com pouca experiência? São dois motivos: o primeiro é que uma das melhores formas de aprender é ensinar algo, então este post está me ajudando a fortalecer o pouco que já conheço. O outro motivo é para poder receber feedback o mais rápido possível, o que deve acelerar meu aprendizado.

Quem me conhece sabe que eu sou muito fã de música, especialmente rock e heavy metal. Isso, aliado a meu amor por tecnologia, gerou várias analogias estranhas no passado:

[Quer melhorar como palestrante? Faça como o Metallica!](https://eltonminetto.dev/2016/06/30/quer-melhorar-como-palestrante-faca-como-o-metallica/)

[Três lições que o AC/DC pode dar para sua carreira](https://eltonminetto.dev/2016/05/29/tres-licoes-que-o-ac-slash-dc-pode-dar-para-sua-carreira/)

[Se as empresas fossem bandas de rock](https://eltonminetto.dev/2014/09/17/se-as-empresas-fossem-bandas-de-rock/)

[Frameworks PHP e analogias](https://eltonminetto.dev/2013/11/04/frameworks-php-e-analogias/)

[Programador Dave Grohl e não Axl Rose](https://eltonminetto.dev/2013/07/31/programador-dave-grohl-e-nao-axl-rose/)

Então, seguindo essa minha tradição, aqui vai mais uma analogia:

> Go é o Motorhead, Elixir é o Pink Floyd

Esclarecendo um pouco:

- Go é uma linguagem que tem como um dos seus focos a simplicidade. A linguagem tem apenas 25 palavras reservadas (`break`, `case`, `for`, etc), não existe mais de uma forma de se fazer a mesma coisa (não existe `loop`, `while`, `until`, etc, apenas `for`). Mas não se engane ao pensar que essa simplicidade é sinônimo de pouco poder, afinal Docker, Kubernetes, e [centenas de empresas](https://github.com/golang/go/wiki/GoUsers) usam a linguagem para desenvolver produtos complexos. A simplicidade é uma decisão de projeto. Da mesma forma, o Mothorhead era um power trio (guitarra, baixo e bateria) e eles entraram para a história como uma das bandas mais importantes do rock, influenciando gerações inteiras.
  [![motorhead](/images/posts/motorhead.jpg)](/images/posts/motorhead.jpg)

- Elixir é uma linguagem rebuscada, com construções mais complexas, dando muito mais liberdade para o desenvolvedor criar suas soluções. Além disso, é uma linguagem funcional, o que permite soluções mais elegantes para diversos problemas. Assim como o Pink Floyd foi um dos expoentes do chamado rock progressivo, com composições complexas e elegantes.
  [![pink_floyd](/images/posts/pink_floyd.jpg)](/images/posts/pink_floyd.jpg)

Tradição respeitada, vamos para o restante do post

## O que é parecido

**Tratamento de erros**

Apesar de Elixir ter mais formas de tratar erros, uma delas me lembrou bastante o que é usado na linguagem Go:

```go
//a função os.Open retorna o acesso ao arquivo
//ou um erro caso contrário
file, err := os.Open("file.go")
if err != nil {
	log.Fatal(err)
}
```

Em Elixir a construção é similar:

```elixir
iex> File.read("path/to/existing/file")
{:ok, "... contents ..."}
iex> File.read("path/to/unknown/file")
{:error, :enoent}
```

Esse formato é bem comum em várias funções que encontrei nos meus estudos. O `:ok` e o `:error` são [Atoms](https://elixir-lang.org/getting-started/basic-types.html#atoms), uma constante cujo valor é seu próprio nome.

## O que é diferente

A comunidade Elixir gosta muito de frameworks, ao contrário da comunidade Go, que prefere usar ao máximo a biblioteca padrão.

O lado bom disso é que existem frameworks muito maduros, como o [Phoenix](https://phoenixframework.org/). O lado ruim é que para começar a fazer algo útil você precisa aprender além da linguagem e dos conceitos funcionais um framework como o Phoenix (rotas, o wrapper de banco de dados [Ecto](https://hexdocs.pm/ecto/Ecto.html), etc). Isso é uma bela duma sobrecarga inicial.

## O que eu gostei

**Pattern Matching**

_Pattern maching_ é uma das features mais poderosas e assim como muitas outras herdada da Erlang, que é a linguagem em que Elixir se baseia.

Nestes dois posts o [Philip Sampaio](http://philipsampaio.com.br/) faz uma ótima introdução:

- [Usando pattern matching e recursividade com Elixir](http://philipsampaio.com.br/blog/2014/06/14/usando-pattern-matching-e-recursividade-com-elixir/)
- [10 exemplos de pattern matching em Elixir](http://philipsampaio.com.br/blog/2015/01/08/10-exemplos-de-pattern-matching-em-elixir/)

Algo legal que é possível fazer com o _pattern matching_ é criar _guard clauses_, que deixam o código bem mais elegante.

Um exemplo simples, em Go:

```go
func factorial(n uint64)(result uint64) {
   if (n > 0) {
      result = n * factorial(n-1)
      return result
   }
   return 1
}
```

Seria escrito em Elixir da seguinte forma:

```elixir
defmodule Factorial do
 def of(0), do: 1
 def of(n) when is_integer(n) and n > 0 do
	 n * of(n-1)
 end
end
```

Mais exemplos podem ser vistos nessa documentação: [https://hexdocs.pm/elixir/guards.html](https://hexdocs.pm/elixir/guards.html)

**Pipes**

Pipes são uma forma bem interessante de aplicar um conceito bem antigo, o mesmo usado pelo Unix. Pequenas funções (programas no caso do Unix) que fazem apenas uma coisa, mas que usadas em conjunto permitem a criação de funcionalidades complexas.

Por exemplo, em Go:

```go
//pseudo-código, simplificado ;)
people,err := findCustomers()
orders,err := findOrders(people)
tax,err := salesTax(orders, 2018)
filing,err: = prepareFiling(tax)
```

Em Elixir poderia ser escrito da seguinte forma:

```elixir
filing = DB.find_customers
   |> Orders.for_customers
   |> sales_tax(2018)
   |> prepare_filing
```

Resumindo, o resultado de uma função é passado como o primeiro parâmetro da próxima. Mais exemplos podem ser encontrados na documentação: [https://elixirschool.com/en/lessons/basics/pipe-operator/](https://elixirschool.com/en/lessons/basics/pipe-operator/)

## O que eu não gostei

- Muita mágica. Provavelmente essa sensação é comum para quem está iniciando na linguagem, mas principalmente ao trabalhar com o Phoenix, fiquei me perguntando "de onde veio essa função?". Em Go as coisas são muito mais explícitas (alguns diriam que até demais hehe)
- Muitas formas de se fazer a mesma coisa. Ao mesmo tempo que isso pode ser bom, permitindo algoritmos mais complexos, é ruim porque permite algoritmos mais complexos :) Ter apenas uma forma de se fazer as coisas mais básicas ajuda bastante para quem está iniciando na linguagem, e até nos code reviews conforme o projeto evolui.
- O código é pouco legível. O que é retornado da função? Como?

Ahh como faz falta um `return` a definição do tipo das variáveis... Olhando para o código:

```elixir
defp do_update_car(car, params) do
    car
    |> Car.changeset(params)
    |> Repo.update()
  end
```

Me parece bem mais complexo, para um iniciante, identificar os possíveis valores dos parâmetros e o que vai ser retornado. A tipagem forte do Go ajuda muito no aprendizado e no code review, principalmente para novos desenvolvedores

- A IDE. O Visual Studio Code tem [ótimos plugins](https://github.com/elixir-lsp/vscode-elixir-ls) para se trabalhar com Elixir, mas as ferramentas para Go são muito mais avançadas, principalmente o [Goland](https://www.jetbrains.com/go/promo/?gclid=EAIaIQobChMIqJrR-4aF6wIVgovICh1LPgMUEAAYASAAEgIzKvD_BwE), da Jetbrains. O autocomplete, debug e inspect das funções é muito mais eficiente, provavelmente devido a tipagem forte da linguagem
- Documentação e posts antigos. A grande maioria dos posts que encontrei são antigos, poucos de 2019 mas uma grande quantidade mais antiga do que 2018. Mas isso não necessariamente é algo ruim, pode ser que as mudanças da linguagem/comunidade tenham um ritmo menos frenético do que a comunidade Go.

# Conclusões

Aprender uma linguagem é sempre uma mistura de alegrias e frustrações. Ainda tenho muito chão pela frente para me sentir produtivo na linguagem, mas as primeiras impressões foram bem legais.

Se você é dev Go e quer começar a aprender Elixir, eu recomendo a leitura dos [guias do site oficial](https://elixir-lang.org/getting-started/introduction.html) como um bom começo. Após a leitura dos guias eu li [este livro](https://www.amazon.com.br/Functional-Web-Development-Elixir-Phoenix-ebook/dp/B079ZN5HS7/ref=sr_1_1?__mk_pt_BR=%C3%85M%C3%85%C5%BD%C3%95%C3%91&dchild=1&keywords=functional+web+development+with+elixir&qid=1596661522&sr=8-1) e me recomendaram [este](https://www.amazon.com.br/Programming-Elixir-1-6-Dave-Thomas/dp/1680502999/ref=sr_1_1?__mk_pt_BR=%C3%85M%C3%85%C5%BD%C3%95%C3%91&dchild=1&keywords=programming+elixir&qid=1596661590&sr=8-1). Aliás, o preço dos livros é outro item da lista "Não Gostei"...

Se você conhece Elixir e encontrou erros no meu texto por favor comente abaixo, para me ajudar a aprender da forma correta.

P.S.: se eu gosto mais de Motorhead ou Pink Floyd? Talvez essa foto do meu gato Lemmy dê uma dica ;)

[![lemmy](/images/posts/lemmy.jpg)](/images/posts/lemmy.jpg)
