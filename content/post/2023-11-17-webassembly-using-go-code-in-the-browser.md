---
title: "WebAssembly: usando código Go no navegador"
date: 2023-11-17T08:00:43-03:00
draft: false
tags:
  - go
---

De tempos em tempos surge uma tecnologia que causa um grande impacto no dia a dia das pessoas desenvolvedoras. Linux, Git, Docker, Kubernetes, entre outras. Na minha opinião o WebAssembly é uma tecnologia que tem potencial para figurar nessa seleta lista.

> WebAssembly (também conhecido como WASM) foi lançado em 2017 como um formato de instrução binária para uma máquina virtual baseada em pilha, desenvolvida para ser executada em navegadores da web modernos para fornecer “execução eficiente e representação compacta de código em processadores modernos, inclusive em um navegador da web. ”
>
> > [Fonte](https://thenewstack.io/webassembly/what-is-webassembly-and-why-do-you-need-it/)

Como fala a definição, por ser um "formato de instrução binária" podemos executar código gerado por qualquer linguagem de programação que seja capaz de gerar este formato. Neste post vamos fazer isso com Go.

Para este teste eu procurei um código que fizesse uso de algum recurso importante da linguagem, como _goroutines_ e _channels_. Lembrei do motivo pelo qual eu comecei a usar Go, lá em 2015. Eu queria executar algumas simulações baseadas no conceito do [Método de Monte Carlo](https://en.wikipedia.org/wiki/Monte_Carlo_method) e a facilidade de concorrência de Go foi perfeita para resolver esse problema. Pesquisei um pouco e encontrei um [exemplo perfeito](https://www.soroushjp.com/2015/02/07/go-concurrency-is-not-parallelism-real-world-lessons-with-monte-carlo-simulations/) para o que eu gostaria de testar. Fiz algumas pequenas mudanças e o código ficou desta forma:

```go
package main

import (
	"fmt"
	"math/rand"
	"runtime"
	"time"
)

func main() {
	fmt.Println(pi(10000))
}

func pi(samples int) float64 {
	cpus := runtime.NumCPU()

	threadSamples := samples / cpus
	results := make(chan float64, cpus)

	for j := 0; j < cpus; j++ {
		go func() {
			var inside int
			r := rand.New(rand.NewSource(time.Now().UnixNano()))
			for i := 0; i < threadSamples; i++ {
				x, y := r.Float64(), r.Float64()

				if x*x+y*y <= 1 {
					inside++
				}
			}
			results <- float64(inside) / float64(threadSamples) * 4
		}()
	}

	var total float64
	for i := 0; i < cpus; i++ {
		total += <-results
	}

	return total / float64(cpus)
}

```

Com o Método de Monte Carlo, quanto mais simulações são executadas mais preciso é o resultado, então performance e concorrência são cruciais para a eficiência do algoritmo.

Vamos agora fazer algumas alterações no código para que seja possível executá-lo no navegador.

```go
package main

import (
	"math/rand"
	"runtime"
	"syscall/js"
	"time"
)

func main() {
	js.Global().Set("jsPI", jsPI())
	<-make(chan bool)
}

func pi(samples int) float64 {
	cpus := runtime.NumCPU()

	threadSamples := samples / cpus
	results := make(chan float64, cpus)

	for j := 0; j < cpus; j++ {
		go func() {
			var inside int
			r := rand.New(rand.NewSource(time.Now().UnixNano()))
			for i := 0; i < threadSamples; i++ {
				x, y := r.Float64(), r.Float64()

				if x*x+y*y <= 1 {
					inside++
				}
			}
			results <- float64(inside) / float64(threadSamples) * 4
		}()
	}

	var total float64
	for i := 0; i < cpus; i++ {
		total += <-results
	}

	return total / float64(cpus)
}

func jsPI() js.Func {
	return js.FuncOf(func(this js.Value, args []js.Value) any {
		if len(args) != 1 {
			return "Invalid no of arguments passed"
		}
		samples := args[0].Int()

		return pi(samples)
	})
}

```

A primeira alteração é a criação da função `jsPI()` que vai servir como interface entre o código Go e o JavaScript do navegador. É essa função que iremos invocar via JavaScript.

Na função `main` precisamos incluir a instrução `js.Global().Set("jsPI", jsPI())` para que seja possível invocar o `jsPI` a partir do JavaScript. Também é necessário incluir o trecho `<-make(chan bool)` para que o código continue executando ou ele vai ser finalizado antes de ser invocado pelo JavaScript, gerando um erro no console do navegador.

O próximo passo é realizarmos a compilação usando o comando:

```bash
GOARCH=wasm GOOS=js go build -o pi.wasm
```

O resultado é um binário no formato esperado pelo WebAssembly.

Vamos agora criar o HTML e o JavaScript qua vai fazer a invocação do código Go. Para isso precisamos incluir no nosso projeto um `js` que é fornecido pela linguagem Go, com o comando:

```bash
cp "$(go env GOROOT)/misc/wasm/wasm_exec.js" .
```

O código do nosso `index.html` ficou desta forma:

```html
<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Go + WebAssembly Example</title>
  </head>

  <body>
    <script src="/wasm_exec.js"></script>
    <script>
      function pi() {
        const go = new Go();
        WebAssembly.instantiateStreaming(fetch("pi.wasm"), go.importObject).then((result) => {
          go.run(result.instance);
          v = jsPI(parseInt(document.getElementById("simulations").value));
          document.getElementById("result").textContent = v;
        });
      }
    </script>
  </body>
  <form>
    Simulations: <input type="text" id="simulations" />
    <input type="button" value="Calculate" onclick="pi()" />
    <div id="result"></div>
  </form>
</html>
```

Precisamos de um servidor web para entregar os arquivos `html`, `js` e `wasm`. Para este fim podemos usar qualquer servidor como o Caddy, Nginx, ou mesmo uma aplicação escrita em Go. Para tornar o exemplo mais simples eu optei por usar o servidor web embutido na linguagem Python, que é nativa do meu macOS:

```bash
python3 -m http.server
```

Agora basta acessar o endereço `http://localhost:8000` no navegador, preencher o número de simulações no formulário e visualizar o resultado:

[![wasm](/images/posts/wasm.png)](/images/posts/wasm.png)

E temos um algoritmo concorrente, escrito em Go, executando nativamente no nosso navegador. Isso abre possibilidades incríveis, desde o uso de algoritmos complexos até bibliotecas gráficas ou de games. Além disso, podemos criar aplicações Web usando componentes escritos em Go, Rust, Java, etc. Reaproveitamento de código é sempre uma boa prática.

Um único ponto a ser considerado neste exemplo é o tamanho do binário gerado:

```bash
❯ ls -lha
total 3432
drwxr-xr-x   7 eminetto  staff   224B 17 Nov 08:47 .
drwxr-xr-x  65 eminetto  staff   2,0K 17 Nov 08:22 ..
-rw-r--r--@  1 eminetto  staff    51B 17 Nov 08:22 go.mod
-rw-r--r--   1 eminetto  staff   732B 17 Nov 08:41 index.html
-rw-r--r--   1 eminetto  staff   894B 17 Nov 08:31 main.go
-rwxr-xr-x   1 eminetto  staff   1,6M 17 Nov 08:31 pi.wasm
-rw-r--r--@  1 eminetto  staff    16K 17 Nov 08:39 wasm_exec.js
```

O `pi.wasm` tem `1.6m` de tamanho, o que pode ser um problema dependendo do caso. Uma solução para resolver este problema é usar o [TinyGo](https://tinygo.org/docs/guides/webassembly/) que é uma versão da linguagem para ser usada em ambientes de IoT e WebAssembly. Ela propositalmente possui menos recursos do que a linguagem original, mas permite a geração de binários muito pequenos. Essa solução vem sendo usada em alguns cenários, como os que vou citar nos próximos posts desta série.

Espero que este primeiro post sirva para instigá-lo a testar o WebAssembly e deixá-lo curioso para acompanhar os próximos textos que quero escrever sobre o assunto ;)
