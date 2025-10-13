---
title: "Testando o Generics do Go"
date: 2022-03-11T08:27:10-03:00
draft: false
tags:
  - go
---

Finalmente está (quase) entre nós! Depois de anos ouvindo aquela piadinha "e o Generics?" esta aguardada funcionalidade vai estar disponível na versão 1.18 da linguagem, prevista para lançamento em Março de 2022.

[![e o Generics?](/images/posts/E_o_PT_hein_E_o_Lula.gif)](/images/posts/E_o_PT_hein_E_o_Lula.gif)

Neste post eu vou fazer um exemplo usando Generics e um pequeno benchmark para conferir se existem diferenças de performance entre uma função "normal" e outra usando esta nova funcionalidade.

Para isso eu me inspirei na biblioteca [lo](https://github.com/samber/lo), uma das primeiras que usa Generics e que ganhou destaque recentemente por implementar várias funcionalidades úiteis para slices e maps.

O primeiro passo foi instalar o Go 1.18, que no momento da escrita deste post encontra-se na versão Release Canditate 1. Para isso eu segui essa [documentação](https://groups.google.com/g/golang-announce/c/QHL1fTc352o/m/5sE6moURBwAJ?pli=1) e executei os comandos:

```
go install golang.org/dl/go1.18rc1@latest
go1.18rc1 download
```

Com isso foi criado o diretório `sdk` na home do meu usuário no Mac. Vamos usar esse diretório para configurar a IDE, para que ela reconheça a nova versão da linguagem. Eu estou usando o Goland da Jetbrains, então minha configuração ficou desta forma:

[![generics_goland](/images/posts/generics_goland.png)](/images/posts/generics_goland.png)

Além de criar o diretório `sdk` os comandos acima criaram o binário `go1.18rc1` no diretório `go/bin` da home do meu usuário no Mac. É esse binário que vamos usar para rodar os testes:

```
eminetto@MacBook-Pro-da-Trybe ~/D/post-generics [1]> go1.18rc1 version
go version go1.18rc1 darwin/arm64
```

O próximo passo foi criar um diretório e um `main.go`:

```
mkdir post-generics
cd post-generics
go1.18rc1 mod init github.com/eminetto/post-generics
touch main.go
```

No `main.go` eu escrevi o seguinte código:

```go
package main

import (
	"fmt"
)

func main() {
	s := []string{"Samuel", "Marc", "Samuel"}
	names := Uniq(s)
	fmt.Println(names)
	names = UniqGenerics(s)
	fmt.Println(names)
	i := []int{1, 20, 20, 10, 1}
	ids := UniqGenerics(i)
	fmt.Println(ids)
}

//from https://github.com/samber/lo/blob/master/slice.go
func UniqGenerics[T comparable](collection []T) []T {
	result := make([]T, 0, len(collection))
	seen := make(map[T]struct{}, len(collection))

	for _, item := range collection {
		if _, ok := seen[item]; ok {
			continue
		}

		seen[item] = struct{}{}
		result = append(result, item)
	}

	return result
}

func Uniq(collection []string) []string {
	result := make([]string, 0, len(collection))
	seen := make(map[string]struct{}, len(collection))

	for _, item := range collection {
		if _, ok := seen[item]; ok {
			continue
		}
		seen[item] = struct{}{}
		result = append(result, item)
	}

	return result
}


```

Na função `main` é possível ver a principal vantagem de Generics, pois usamos a mesma função para remover as entradas duplicadas em slices de strings e de inteiros, sem a necessidade de alteração de código.

Ao executar o código podemos ver o resultado:

```
eminetto@MacBook-Pro-da-Trybe ~/D/post-generics> go1.18rc1 run main.go
[Samuel Marc]
[Samuel Marc]
[1 20 10]
```

Mas e quanto a performance? Estamos perdendo algo ao adicionar essa nova funcionalidade? Para tentar responder isso eu fiz um pequeno benchmark. O primeiro passo foi instalar o pacote `faker`, para gerar mais dados para o benchmark:

```
go1.18rc1 get -u github.com/bxcodec/faker/v3
```

E o código do `main_test.go` ficou desta forma:

```go
package main

import (
	"github.com/bxcodec/faker/v3"
	"testing"
)

var names []string

func BenchmarkMain(m *testing.B) {
	for i := 0; i < 1000; i++ {
		names = append(names, faker.FirstName())
	}
}

func BenchmarkUniq(b *testing.B) {
	_ = Uniq(names)
}

func BenchmarkGenericsUniq(b *testing.B) {
	_ = UniqGenerics(names)
}
```

Executando o benchmark foi possível ver o resultado:

```
eminetto@MacBook-Pro-da-Trybe ~/D/post-generics> go1.18rc1 test -bench=. -benchtime=100x
goos: darwin
goarch: arm64
pkg: github.com/eminetto/post-generics
BenchmarkMain-8                      100               482.1 ns/op
BenchmarkUniq-8                      100              1225 ns/op
BenchmarkGenericsUniq-8              100              1142 ns/op
PASS
ok      github.com/eminetto/post-generics       0.210s
```

Eu executei várias vezes o benchmark e na maioria a versão feita com Generics foi mais performática, apesar da diferença não ter sido tão grande.

## Observações

Este post não é um estudo avançado, com benchmarks cientificamente comprovados, é apenas um teste básico. Então mais fontes devem ser consultadas antes de tomarmos uma decisão final, mas a primeira impressão é que estamos ganhando uma feature importante sem perda perceptível de performance.

Eu acredito que vou esperar a versão final desta funcionalidade estar mais madura, provavelmente depois da 1.18.x, para colocá-la em produção, mas vejo uma grande evolução nas aplicações Go nos próximos meses. A empolgação está começando a aumentar :)

**Update:** O [Tiago Temporin](https://twitter.com/_ttemporin) deu continuidade ao benchmark, [mas olhando para o consumo de memória](https://aprendagolang.com.br/2022/03/16/benchmark-generics-unique-vs-unique/).
