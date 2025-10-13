---
title: "[Go] Como trabalhar com datas em testes"
date: 2023-06-30T08:30:43-03:00
draft: false
tags:
  - go
---

Trabalhar com datas em qualquer linguagem de programação trás alguns desafios interessantes. Neste post vou mostrar uma forma de trabalhar com datas ao escrever testes unitários para uma aplicação em Go.

<!--more-->

Vamos ao exemplo:

```go
import (
	"time"

	"github.com/google/uuid"
)

type Food struct {
	ID             uuid.UUID
	Name           string
	ExpirationDate time.Time
}

func canIEat(f Food) bool {
	if time.Now().Before(f.ExpirationDate) {
		return true
	}
	return false
}
```

Uma forma de escrevermos um teste unitário para este código pode ser:

```go
import (
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

func TestCanIEat(t *testing.T) {
	f1 := Food{
		ExpirationDate: time.Now().AddDate(0, 0, 1),
	}
	assert.True(t, canIEat(f1))

	f2 := Food{
		ExpirationDate: time.Now().AddDate(0, 0, -1),
	}
	assert.False(t, canIEat(f2))
}
```

Outra forma de se resolver isso é criarmos uma abstração para o pacote `time`. Para isso vamos criar um novo pacote, chamado `clock` e dentro dele criamos o arquivo `clock.go`:

```go
package clock

import "time"

// Clock interface
type Clock interface {
	Now() time.Time
}

// RealClock clock
type RealClock struct{}

// NewRealClock create a new real clock
func NewRealClock() *RealClock {
	return &RealClock{}
}

// Now returns the current data
func (c *RealClock) Now() time.Time {
	return time.Now()
}
```

O próximo passo é refatorar a função que vai usar o novo pacote:

```go
import (
	"time"

	"github.com/eminetto/post-time/clock"
	"github.com/google/uuid"
)

type Food struct {
	ID             uuid.UUID
	Name           string
	ExpirationDate time.Time
}

func canIEat(c clock.Clock, f Food) bool {
	if c.Now().Before(f.ExpirationDate) {
		return true
	}
	return false
}
```

Como a função `canIEat` recebe a interface `clock.Clock` podemos, no nosso teste, enviar uma nova implementação desta interface:

```go
import (
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

type FakeClock struct{}

func (c FakeClock) Now() time.Time {
	return time.Date(2023, 6, 30, 20, 0, 0, 0, time.Local)
}

func TestCanIEat(t *testing.T) {
	type test struct {
		food     Food
		expected bool
	}
	fc := FakeClock{}
	cases := []test{
		{
			food: Food{
				ExpirationDate: time.Date(2020, 10, 1, 20, 0, 0, 0, time.Local),
			},
			expected: false,
		},
		{
			food: Food{
				ExpirationDate: time.Date(2023, 6, 30, 20, 0, 0, 0, time.Local),
			},
			expected: false,
		},
		{
			food: Food{
				ExpirationDate: time.Date(2023, 6, 30, 21, 0, 0, 0, time.Local),
			},
			expected: true,
		},
	}
	for _, c := range cases {
		assert.Equal(t, c.expected, canIEat(fc, c.food))
	}
}

```

Desta forma temos um controle melhor do que será usado no teste, além de ganharmos performance pois não é mais preciso fazer cálculos de data como o `time.Now().AddDate(0, 0, 1)` do primeiro exemplo.

Essa é uma dica simples mas que mostra como é poderoso e fácil de usar o conceito de [interfaces em Go](https://eltonminetto.dev/post/2022-06-07-using-go-interfaces/).
