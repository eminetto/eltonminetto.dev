---
title: "[Go] How to work with dates in tests"
date: 2023-06-30T08:30:43-03:00
draft: false
tags:
  - go
---

Working with dates in any programming language presents some challenges. In this post, I will show how to work with dates when writing unit tests for a Go application.

<!--more-->

Let's go to the example:

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

One way to write a unit test for this code could be:

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

Another way to solve this is to create an abstraction for the `time` package. For this, we will create a new package called `clock`, inside it, we will add the `clock.go` file:

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

The next step is to refactor the function that will use the new package:

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

As the `canIEat` function receives the `clock.Clock` interface, we can, in our test, use a new implementation of this interface:

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

This way, we better control what will we are using in the test and gain performance because it is no longer necessary to do date calculations like the `time.Now().AddDate(0, 0, 1)` of the first example.

What I'm doing here is a simple tip, but it shows how powerful and easy to use the concept of [interfaces in Go](https://eltonminetto.dev/en/post/2022-06-07-using-go-interfaces/) is.
