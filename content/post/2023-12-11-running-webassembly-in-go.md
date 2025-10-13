---
title: "Executando código WebAssembly em Go"
date: 2023-12-11T08:00:43-03:00
draft: false
tags:
  - go
---

Este é o segundo post de uma série que estou fazendo sobre WebAssembly e Go. No [primeiro post](https://eltonminetto.dev/post/2023-11-17-webassembly-using-go-code-in-the-browser/) vimos como executar código Go em um navegador web. Neste vamos importar uma função WebAssembly e executá-la em uma aplicação Go.

Provavelmente este vai ser o texto mais curto da série, pois o processo é realmente bem simples :)

O primeiro passo foi criar alguma função em WebAssembly e neste caso aproveitei para testar algo em Rust, uma linguagem que tenho planos de aprender em 2024. Para isso segui o passo a passo que encontrei no site [Wasm By Example](https://wasmbyexample.dev/examples/hello-world/hello-world.rust.en-us.html). Ao final dos passos você vai ter um arquivo `.wasm` para importar no seu projeto Go, no meu caso o nome do arquivo ficou `poc_wasm_in_go_bg.wasm` pois ele é gerado de acordo com o nome do diretório do projeto.

O próximo passo é criarmos um projeto Go e usar algum `runtime` para executar nosso arquivo `wasm`. Para isso escolhi o [wasmer-go](https://github.com/wasmerio/wasmer-go). O que eu fiz basicamente foi:

```bash
mkdir go-project
cd go-project
go mod init github.com/eminetto/go-project
go get github.com/wasmerio/wasmer-go/wasmer
go mod tidy
```

E criei um arquivo `main.go` com o conteúdo:

```go
package main

import (
	"fmt"
	"os"

	wasmer "github.com/wasmerio/wasmer-go/wasmer"
)

func main() {
	wasmBytes, _ := os.ReadFile("path_to_file/poc_wasm_in_go_bg.wasm")

	engine := wasmer.NewEngine()
	store := wasmer.NewStore(engine)

	// Compiles the module
	module, _ := wasmer.NewModule(store, wasmBytes)

	// Instantiates the module
	importObject := wasmer.NewImportObject()
	instance, _ := wasmer.NewInstance(module, importObject)

	// Gets the `sum` exported function from the WebAssembly instance.
	add, _ := instance.Exports.GetFunction("add")

	// Calls that exported function with Go standard values. The WebAssembly
	// types are inferred and values are casted automatically.
	result, _ := add(5, 37)

	fmt.Println(result) // 42!
}
```

Agora é só executar o código:

```bash
❯ go run main.go
42
```

Simples assim :) Temos um código escrito em Rust, compilado para WebAssembly sendo executado como se fosse uma função nativa em Go.

**E a performance?**

Para responder essa pergunta comecei fazendo uma refatoração no `main.go`:

```go
package main

import (
	"fmt"
	"os"

	wasmer "github.com/wasmerio/wasmer-go/wasmer"
)

func main() {
	add, err := loadWasmFunc("path_to_file/poc_wasm_in_go_bg.wasm")
	if err != nil {
		panic(err)
	}
	wasmAdd(add, 50, 31)
}

func loadWasmFunc(fileName string) (wasmer.NativeFunction, error) {
	wasmBytes, err := os.ReadFile(fileName)
	if err != nil {
		return nil, err
	}

	engine := wasmer.NewEngine()
	store := wasmer.NewStore(engine)

	// Compiles the module
	module, err := wasmer.NewModule(store, wasmBytes)
	if err != nil {
		return nil, err
	}

	// Instantiates the module
	importObject := wasmer.NewImportObject()
	instance, err := wasmer.NewInstance(module, importObject)
	if err != nil {
		return nil, err
	}

	// Gets the `sum` exported function from the WebAssembly instance.
	add, _ := instance.Exports.GetFunction("add")
	return add, nil
}

func wasmAdd(add wasmer.NativeFunction, a, b int) {
	result, _ := add(a, b)
	fmt.Println(result)
}

func add(a, b int) {
	result := a + b
	fmt.Println(result)
}

```

O objetivo foi separar a carga do arquivo `wasm` da execução da função. Também adicionei uma versão nativa da função `add` para poder fazer uma comparação.

Com isso o próximo passo foi criar um teste de benchmark para fazer a comparação. O arquivo `main_test.go` ficou da seguinte forma:

```go
package main

import "testing"

func BenchmarkWebAssemblyAdd(b *testing.B) {
	add, err := loadWasmFunc("poc_wasm_in_go_bg.wasm")
	if err != nil {
		b.Fail()
	}
	for n := 0; n > b.N; n++ {
		wasmAdd(add, 50, n)
	}
}

func BenchmarkNativeAdd(b *testing.B) {
	for n := 0; n > b.N; n++ {
		add(50, n)
	}
}

```

Ao executar com o comando:

```bash
❯ go test -bench=. -cpu=8 -benchmem -benchtime=5s -count 5
```

Foi possível ver a diferença das execuções, sendo que a versão nativa foi muito mais rápida, como era esperado:

[![webassembly_benchmark](/images/posts/webassembly_benchmark.png)](/images/posts/webassembly_benchmark.png)

Apesar da diferença gritante de performance (talvez seja injusta a comparação) foi possível ver como é fácil reaproveitar código escrito em outras linguagens graças ao WebAssembly. Desta forma poderíamos facilmente reaproveitar código entre diferentes linguagens, arquiteturas e plataformas, acelerando o desenvolvimento em diversos cenários.

Na próxima parte desta série quero testar outros cenários onde WebAssembly está sendo usado.
