---
title: "Criando mocks para testes usando GoMock"
subtitle: ""
date: "2019-12-19T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

O uso de [mocks](https://pt.wikipedia.org/wiki/Objeto_Mock) no desenvolvimento de testes é um conceito usado na grande maioria das linguagens de programação. Neste post vou falar sobre uma das soluções para implementar mocks em Go, o [GoMock](https://github.com/golang/mock).

Para demonstrar as funcionalidades do GoMock vou usar os testes criados no meu [repositório](https://github.com/eminetto/clean-architecture-go) sobre [Clean Architecture](https://eltonminetto.dev/en/post/2018-03-05-clean-architecture-using-go/).

Como a Clean Architecture incentiva a criação de testes em todas as camadas é fácil perceber onde podemos usar mocks para facilitar o desenvolvimento. Como escrevemos [testes unitários para a camada de `UseCases`](https://github.com/eminetto/clean-architecture-go/blob/master/pkg/bookmark/service_test.go) temos a certeza que a lógica contida nesta camada está coberta por testes. Na camada de `Controller` podemos usar mocks para simular o uso dos `UseCases` pois sabemos que sua funcionalidade já está validada.

Vamos criar mocks para esta camada, que é representada pelas [interfaces](https://github.com/eminetto/clean-architecture-go/blob/master/pkg/bookmark/interface.go):

```go
package bookmark

import "github.com/eminetto/clean-architecture-go/pkg/entity"

//Reader interface
type Reader interface {
	Find(id entity.ID) (*entity.Bookmark, error)
	Search(query string) ([]*entity.Bookmark, error)
	FindAll() ([]*entity.Bookmark, error)
}

//Writer bookmark writer
type Writer interface {
	Store(b *entity.Bookmark) (entity.ID, error)
	Delete(id entity.ID) error
}

//Repository repository interface
type Repository interface {
	Reader
	Writer
}

//UseCase use case interface
type UseCase interface {
	Reader
	Writer
}
```

Para facilitar o uso do GoMock vamos alterar o [arquivo `Makefile`](https://github.com/eminetto/clean-architecture-go/blob/master/Makefile) para adicionar a funcionalidade de geração dos mocks a partir das interfaces. Para isso vamos adicionar:

```bash
build-mocks:
  @go get github.com/golang/mock/gomock
  @go install github.com/golang/mock/mockgen
  @~/go/bin/mockgen -source=pkg/bookmark/interface.go -destination=pkg/bookmark/mock/bookmark.go -package=mock
```

Estes comandos fazem o download do pacote do `gomock` e também do binário `mockgen`, que é usado para gerar os mocks. Após a execução do comando `make build-mocks` o arquivo `pkg/bookmark/mock/bookmark.go` é gerado, com as funções que vamos usar nos testes. É importante lembrar que sempre que forem alteradas as interfaces do arquivo `pkg/bookmark/interface.go` é necessário executar este comando novamente, para que os mocks sejam atualizados.

Vamos agora alterar um dos testes existentes para fazermos uso do mock. No arquivo `api/handler/bookmark_test.go` vamos alterar o teste `TestBookmarkIndex`. O código original era:

```go
func TestBookmarkIndex(t *testing.T) {
  repo := bookmark.NewInmemRepository()
  service := bookmark.NewService(repo)
  r := mux.NewRouter()
  n := negroni.New()
  MakeBookmarkHandlers(r, *n, service)
  path, err := r.GetRoute("bookmarkIndex").GetPathTemplate()
  assert.Nil(t, err)
  assert.Equal(t, "/v1/bookmark", path)
  b := &entity.Bookmark{
    Name:        "Elton Minetto",
    Description: "Minetto's page",
    Link:        "http://www.eltonminetto.net",
    Tags:        []string{"golang", "php", "linux", "mac"},
    Favorite:    true,
  }
  _, _ = service.Store(b)
  ts := httptest.NewServer(bookmarkIndex(service))
  defer ts.Close()
  res, err := http.Get(ts.URL)
  assert.Nil(t, err)
  assert.Equal(t, http.StatusOK, res.StatusCode)
}
```

E o código após alteração ficou da seguinte forma:

```go
func TestBookmarkIndex(t *testing.T) {
  controller := gomock.NewController(t)
  defer controller.Finish()
  service := mock.NewMockUseCase(controller)
  r := mux.NewRouter()
  n := negroni.New()
  MakeBookmarkHandlers(r, *n, service)
  path, err := r.GetRoute("bookmarkIndex").GetPathTemplate()
  assert.Nil(t, err)
  assert.Equal(t, "/v1/bookmark", path)
  b := &entity.Bookmark{
    Name:        "Elton Minetto",
    Description: "Minetto's page",
    Link:        "http://www.eltonminetto.net",
    Tags:        []string{"golang", "php", "linux", "mac"},
    Favorite:    true,
  }
  service.EXPECT().
    FindAll().
    Return([]*entity.Bookmark{b}, nil)
  ts := httptest.NewServer(bookmarkIndex(service))
  defer ts.Close()
  res, err := http.Get(ts.URL)
  assert.Nil(t, err)
  assert.Equal(t, http.StatusOK, res.StatusCode)
}
```

As mudanças foram na instanciação do serviço, onde deixamos de usar a implementação real e passamos a usar o mock:

```go
controller := gomock.NewController(t)
defer controller.Finish()
service := mock.NewMockUseCase(controller)
```

Removemos a linha `_, _ = service.Store(b)` pois agora não precisamos mais incluir um registro antes de usá-lo. E incluímos a configuração do mock:

```go
service.EXPECT().
  FindAll().
  Return([]*entity.Bookmark{b}, nil)
```

Desta forma o mock vai se comportar como o esperado pelo teste em questão. Assim podemos focar em testar apenas o que nos interessa nesta camada, que é a lógica do handler como tratamento do request e do response, rotas, etc.

Além disso, foram necessárias as importações dos pacotes abaixo:

```go
"github.com/eminetto/clean-architecture-go/pkg/bookmark/mock"
"github.com/golang/mock/gomock"
```

No repositório é possível ver os [demais testes](https://github.com/eminetto/clean-architecture-go/blob/master/api/handler/bookmark_test.go).

O uso de mocks não é um consenso na comunidade de desenvolvimento, com algumas pessoas apoiando e outras apontando problemas em algumas abordagens. Venho usando esta técnica nos últimos tempos e gostando do resultado, pois ajuda a manter os testes mais focados, evitando re-testar coisas que já foram validadas com testes unitários ou em outras camadas. Também é útil quando precisamos emular o acesso de um código a um microserviço, biblioteca ou recurso externo.

Como todas as bibliotecas padrão de Go fazem uso extensivo de interfaces é possível criar mocks para muitos recursos como arquivos, bancos de dados, etc. Por isso acredito que soluções como o GoMock podem ser muito úteis em projetos de vários tamanhos.
