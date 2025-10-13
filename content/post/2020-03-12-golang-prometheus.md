---
title: "Usando Prometheus para coletar métricas de aplicações Golang"
subtitle: ""
date: "2020-03-12T08:33:24+02:00"
bigimg: ""
tags:
  - go
---

Este texto faz parte de uma série de posts que estou fazendo com exemplos de aplicações usando a Clean Architecture. Os outros posts que fazem parte desta série são:

- [Clean Architecture using Golang](https://eltonminetto.net/en/post/2018-03-05-clean-architecture-using-go/)
- [Golang: usando build tags para armazenar configurações](https://eltonminetto.dev/post/2018-06-25-golang-usando-build-tags/)
- [Integração contínua em projetos usando monorepo](https://eltonminetto.dev/post/2018-08-01-monorepo-drone/)
- [Migração de dados com Golang e MongoDB](https://eltonminetto.dev/post/2019-01-23-migracao-de-dados-com-go-e-mongodb/)
- [Usando Golang como linguagem de script](https://eltonminetto.dev/post/2019-08-08-golang-linguagem-script/)
- [Criando mocks para testes usando GoMock](https://eltonminetto.dev/post/2019-12-19-usando-gomock/)

Neste post vou falar sobre uma funcionalidade muito importante nos projetos cada vez mais complexos com os quais trabalhamos no dia a dia: a coleta de métricas. Dentre as várias soluções existentes no mercado para este fim, uma das que tem ganhado mais destaque é a dupla [Prometheus](https://prometheus.io) + [Grafana](https://grafana.com).

Segundo a descrição encontrada na Wikipedia:

> Prometheus is a free software application used for event monitoring and alerting. It records real-time metrics in a time series database built using a HTTP pull model, with flexible queries and real-time alerting.

Já o Grafana é descrito como:

> Grafana is a multi-platform open source analytics and interactive visualization software available since 2014. It provides charts, graphs, and alerts for the web when connected to supported data sources.

Resumindo, o Prometheus faz a coleta dos dados e graças ao Grafana podemos criar belos gráficos e dashboards para facilitar a visualização das informações.

## Criando a camada de UseCases

Mas para fazermos uso desta funcionalidade precisamos adaptar nossos códigos para que eles forneçam os dados que o Prometheus possa coletar e processar. Como estamos usando a Clean Architecture, o primeiro passo que vamos fazer é criar um novo pacote na nossa camada de UseCases. Tomando como base o repositório: [https://github.com/eminetto/clean-architecture-go](https://github.com/eminetto/clean-architecture-go) iniciamos criando o arquivo `pkg/metric/interface.go`, cujo conteúdo é:

```go
package metric

import "time"

//CLI define a CLI app
type CLI struct {
	Name       string
	StartedAt  time.Time
	FinishedAt time.Time
	Duration   float64
}

// NewCLI create a new CLI app
func NewCLI(name string) *CLI {
	return &CLI{
		Name: name,
	}
}

//Started start monitoring the app
func (c *CLI) Started() {
	c.StartedAt = time.Now()
}

// Finished app finished
func (c *CLI) Finished() {
	c.FinishedAt = time.Now()
	c.Duration = time.Since(c.StartedAt).Seconds()
}

//HTTP application
type HTTP struct {
	Handler    string
	Method     string
	StatusCode string
	StartedAt  time.Time
	FinishedAt time.Time
	Duration   float64
}

//NewHTTP create a new HTTP app
func NewHTTP(handler string, method string) *HTTP {
	return &HTTP{
		Handler: handler,
		Method:  method,
	}
}

//Started start monitoring the app
func (h *HTTP) Started() {
	h.StartedAt = time.Now()
}

// Finished app finished
func (h *HTTP) Finished() {
	h.FinishedAt = time.Now()
	h.Duration = time.Since(h.StartedAt).Seconds()
}

//UseCase definition
type UseCase interface {
	SaveCLI(c *CLI) error
	SaveHTTP(h *HTTP)
}
```

Neste arquivo fazemos a definição de duas estruturas importantes, a `CLI` e a `HTTP`, que são respectivamente os dados que queremos coletar das nossas aplicações em linha de comando e da nossa API. Também definimos a interface `UseCase`, que vamos implementar na sequência, e funções que inicializam as estruturas: `NewCLI` e `NewHTTP`. Como comentei nos posts anteriores, essa tática da Clean Architecture nos permite abstrair para as outras camadas da aplicação os detalhes da implementação da coleta de métricas. Se em algum momento resolvermos mudar a solução de coleta de métricas do Prometheus para qualquer outra, não teremos problemas, pois as demais camadas esperam receber algo que implemente a interface `UseCase`.

Vamos agora implementar a interface, criando o arquivo `pkg/metric/prometheus.go`:

```go
package metric

import (
	"github.com/prometheus/client_golang/prometheus"
	"github.com/prometheus/client_golang/prometheus/push"
	"github.com/eminetto/clean-architecture-go/config"
)

//Service implements UseCase interface
type Service struct {
	pHistogram           *prometheus.HistogramVec
	httpRequestHistogram *prometheus.HistogramVec
}

//NewPrometheusService create a new prometheus service
func NewPrometheusService() (*Service, error) {
	cli := prometheus.NewHistogramVec(prometheus.HistogramOpts{
		Namespace: "pushgateway",
		Name:      "cmd_duration_seconds",
		Help:      "CLI application execution in seconds",
		Buckets:   prometheus.DefBuckets,
	}, []string{"name"})
	http := prometheus.NewHistogramVec(prometheus.HistogramOpts{
		Namespace: "http",
		Name:      "request_duration_seconds",
		Help:      "The latency of the HTTP requests.",
		Buckets:   prometheus.DefBuckets,
	}, []string{"handler", "method", "code"})

	s := &Service{
		pHistogram:           cli,
		httpRequestHistogram: http,
	}
	err := prometheus.Register(s.pHistogram)
	if err != nil && err.Error() != "duplicate metrics collector registration attempted" {
		return nil, err
	}
	err = prometheus.Register(s.httpRequestHistogram)
	if err != nil && err.Error() != "duplicate metrics collector registration attempted" {
		return nil, err
	}
	return s, nil
}

//SaveCLI send metrics to server
func (s *Service) SaveCLI(c *CLI) error {
	gatewayURL := config.PROMETHEUS_PUSHGATEWAY
	s.pHistogram.WithLabelValues(c.Name).Observe(c.Duration)
	return push.New(gatewayURL, "cmd_job").Collector(s.pHistogram).Push()
}

//SaveHTTP send metrics to server
func (s *Service) SaveHTTP(h *HTTP) {
	s.httpRequestHistogram.WithLabelValues(h.Handler, h.Method, h.StatusCode).Observe(h.Duration)
}
```

Neste arquivo, usando a função `NewPrometheusService` temos uma implementação da interface `UseCase`, que será usada nos próximos passos. Os detalhes de cada função usada pode ser encontrada na [documentação](https://github.com/prometheus/client_golang) do cliente oficial para Go.

Outro ponto importante deste arquivo é a linha `gatewayURL := config.PROMETHEUS_PUSHGATEWAY` que encontra-se dentro da função `SaveCLI`. O Prometheus funciona como um coletor de métricas, então precisamos ter uma forma de armazenar os dados em memória, até que ele faça a coleta. Quando estamos falando de um aplicativo que permanece em execução, como o binário de uma API, estes dados ficam em memória até serem coletados. Mas no caso de uma aplicação CLI, que é finalizada após a execução, temos que armazenar estes dados em algum local. O projeto Prometheus tem uma solução para isso, que chama-se PushGateway. Trata-se de um pequeno aplicativo que devemos manter executando em algum servidor e que vai ser usado para armazenar os dados até serem coletados. Vou falar novamente sobre o PushGateway quando configurarmos o `docker-compose.yml` da aplicação. Nesta configuração, estamos indicando qual é o endereço do PushGateway. Esta variável foi incluída nos arquivos: `config/config_testing.go`, `config/config_staging.go`, `config/config_prod.go` e `config/config_dev.go`. Confira este [post para entender](https://eltonminetto.dev/post/2018-06-25-golang-usando-build-tags/) o motivo da existência destes arquivos. Por exemplo, o arquivo `config/config_dev.go` contém:

```go
// +build dev

package config

const (
	MONGODB_HOST            = "mongodb://127.0.0.1:27017"
	MONGODB_DATABASE        = "bookmark"
	MONGODB_CONNECTION_POOL = 5
	API_PORT                = 8080
	PROMETHEUS_PUSHGATEWAY = "http://localhost:9091/"
)
```

## Coletando métricas de aplicativos CLI

Vamos agora começar a usar o serviço para coletar as métricas do nosso aplicativo CLI. O novo código do arquivo `cmd/main.go` ficou desta forma:

```go
package main

import (
	"errors"
	"fmt"
	"github.com/eminetto/clean-architecture-go/pkg/metric"
	"log"
	"os"

	"github.com/eminetto/clean-architecture-go/config"
	"github.com/eminetto/clean-architecture-go/pkg/bookmark"
	"github.com/eminetto/clean-architecture-go/pkg/entity"
	"github.com/juju/mgosession"
	mgo "gopkg.in/mgo.v2"
)

func handleParams() (string, error) {
	if len(os.Args) < 2 {
		return "", errors.New("Invalid query")
	}
	return os.Args[1], nil
}

func main() {
	metricService, err := metric.NewPrometheusService()
	if err != nil {
		log.Fatal(err.Error())
	}
	appMetric := metric.NewCLI("search")
	appMetric.Started()
	query, err := handleParams()
	if err != nil {
		log.Fatal(err.Error())
	}

	session, err := mgo.Dial(config.MONGODB_HOST)
	if err != nil {
		log.Fatal(err.Error())
	}
	defer session.Close()

	mPool := mgosession.NewPool(nil, session, config.MONGODB_CONNECTION_POOL)
	defer mPool.Close()

	bookmarkRepo := bookmark.NewMongoRepository(mPool, config.MONGODB_DATABASE)
	bookmarkService := bookmark.NewService(bookmarkRepo)
	all, err := bookmarkService.Search(query)
	if err != nil {
		log.Fatal(err)
	}
	if len(all) == 0 {
		log.Fatal(entity.ErrNotFound.Error())
	}
	for _, j := range all {
		fmt.Printf("%s %s %v \n", j.Name, j.Link, j.Tags)
	}
	appMetric.Finished()
	err = metricService.SaveCLI(appMetric)
	if err != nil {
		log.Fatal(err)
	}
}

```

No começo da função `main` estamos inicializando o serviço com a implementação para o Prometheus:

```go
metricService, err := metric.NewPrometheusService()
if err != nil {
	log.Fatal(err.Error())
}
```

Logo em seguida iniciamos a coleta, dando um nome para nosso aplicativo, que será usado na visualização no Grafana:

```go
appMetric := metric.NewCLI("search")
appMetric.Started()
```

E no final do arquivo fazemos a finalização e o envio dos dados para o `PushGateway`:

```go
appMetric.Finished()
err = metricService.SaveCLI(appMetric)
if err != nil {
	log.Fatal(err)
}
```

# Coletando métricas da API

Agora vamos coletar as métricas da nossa API. Como queremos coletar métricas de todos os `endpoints`, podemos fazer uso do conceito de [middlewares](https://eltonminetto/dev/files/talks/middlewaresgo-161106214633.pdf). Para isso vamos criar o arquivo `pkg/middleware/metrics.go`:

```go
package middleware

import (
	"net/http"
	"strconv"

	"github.com/eminetto/clean-architecture-go/pkg/metric"

	"github.com/codegangsta/negroni"
)

//Metrics to prometheus
func Metrics(mService metric.UseCase) negroni.HandlerFunc {
   return func(w http.ResponseWriter, r *http.Request, next http.HandlerFunc) {
      appMetric := metric.NewHTTP(r.URL.Path, r.Method)
      appMetric.Started()
      next(w, r)
      res := w.(negroni.ResponseWriter)
      appMetric.Finished()
      appMetric.StatusCode = strconv.Itoa(res.Status())
      mService.SaveHTTP(appMetric)
   }
}
```

Este middleware vai receber uma implementação da interface `metric.UseCase`, inicializar a coleta dos dados da requisição (tempo de execução e status code) e salvar os dados para futura coleta. Como estamos falando de uma API, uma aplicação que vai permanecer em execução, este armazenamento é feito em memória, até que o Prometheus faça a coleta e o processamento.

Precisamos agora alterar o `main.go` da nossa API, para fazermos uso do novo middleware e para criarmos o endpoint que o Prometheus vai usar para coletar as métricas. O arquivo `api/main.go` ficou desta forma:

```go
package main

import (
   "github.com/prometheus/client_golang/prometheus/promhttp"
   "log"
   "net/http"
   "os"
   "strconv"
   "time"

   "github.com/codegangsta/negroni"
   "github.com/eminetto/clean-architecture-go/api/handler"
   "github.com/eminetto/clean-architecture-go/config"
   "github.com/eminetto/clean-architecture-go/pkg/bookmark"
   "github.com/eminetto/clean-architecture-go/pkg/middleware"
   "github.com/eminetto/clean-architecture-go/pkg/metric"
   "github.com/gorilla/context"
   "github.com/gorilla/mux"
   "github.com/juju/mgosession"
   mgo "gopkg.in/mgo.v2"
)

func main() {
   session, err := mgo.Dial(config.MONGODB_HOST)
   if err != nil {
      log.Fatal(err.Error())
   }
   defer session.Close()

   r := mux.NewRouter()

   mPool := mgosession.NewPool(nil, session, config.MONGODB_CONNECTION_POOL)
   defer mPool.Close()

   bookmarkRepo := bookmark.NewMongoRepository(mPool, config.MONGODB_DATABASE)
   bookmarkService := bookmark.NewService(bookmarkRepo)

   metricService, err := metric.NewPrometheusService()
   if err != nil {
      log.Fatal(err.Error())
   }

   //handlers
   n := negroni.New(
      negroni.HandlerFunc(middleware.Cors),
      negroni.HandlerFunc(middleware.Metrics(metricService)),
      negroni.NewLogger(),
   )
   //bookmark
   handler.MakeBookmarkHandlers(r, *n, bookmarkService)

   http.Handle("/", r)
   http.Handle("/metrics", promhttp.Handler())
   r.HandleFunc("/ping", func(w http.ResponseWriter, r *http.Request) {
      w.WriteHeader(http.StatusOK)
   })

   logger := log.New(os.Stderr, "logger: ", log.Lshortfile)
   srv := &http.Server{
      ReadTimeout:  5 * time.Second,
      WriteTimeout: 10 * time.Second,
      Addr:         ":" + strconv.Itoa(config.API_PORT),
      Handler:      context.ClearHandler(http.DefaultServeMux),
      ErrorLog:     logger,
   }
   err = srv.ListenAndServe()
   if err != nil {
      log.Fatal(err.Error())
   }
}
```

A primeira mudança importante, além dos imports necessários e da inicialização do serviço (como fizemos no CLI), foi a inclusão do nosso novo middleware na pilha de execuções, nas linhas:

```go
n := negroni.New(
  negroni.HandlerFunc(middleware.Cors),
  negroni.HandlerFunc(middleware.Metrics(metricService)),
  negroni.NewLogger(),
)
```

E a segunda alteração foi a criação de um endpoint que será usado pelo Prometheus:

```go
http.Handle("/metrics", promhttp.Handler())
```

Esta é toda a alteração necessária na nossa aplicação para que os dados sejam coletados pelo Prometheus. Vamos agora configurar um ambiente local para facilitar os testes.

## Adicionando o Prometheus e o Grafana no ambiente de desenvolvimento

Como estamos usando Docker para gerenciar nosso ambiente de desenvolvimento, vamos alterar o arquivo `docker-compose.yml` para adicionar as novas dependências. O arquivo alterado ficou desta forma:

```yml
version: "3"
services:
  mongodb:
    image: mongo
    ports:
      - "27017:27017"
    container_name: bookmark-mongodb
    network_mode: "bridge"
  node:
    image: node:8-alpine
    network_mode: "bridge"
    volumes:
      - ./web:/web
      - /tmp:/tmp
  grafana:
    image: grafana/grafana
    ports:
      - "3000:3000"
    container_name: bookmark-grafana
    network_mode: "bridge"
    depends_on:
      - prometheus
      - prometheus-pushgateway
  prometheus:
    image: prom/prometheus
    ports:
      - 9090:9090
    command:
      - --config.file=/etc/prometheus/prometheus.yml
    volumes:
      - ./infra/prometheus/prometheus.yml:/etc/prometheus/prometheus.yml:ro
    container_name: bookmark-prometheus
    network_mode: "bridge"
  prometheus-pushgateway:
    image: prom/pushgateway
    container_name: bookmark-pushgateway
    expose:
      - 9091
    ports:
      - "9091:9091"
```

Adicionamos as configurações referentes aos serviços `grafana`, `prometheus` e o `prometheus-pushgateway`. Como é possível ver na configuração do `prometheus`, precisamos também criar um arquivo com suas configurações. O arquivo `infra/prometheus/prometheus.yml` criado foi:

```yml
# my global config
global:
  scrape_interval: 15s # Set the scrape interval to every 15 seconds. Default is every 1 minute.
  evaluation_interval: 15s # Evaluate rules every 15 seconds. The default is every 1 minute.

# Alertmanager configuration
alerting:
  alertmanagers:
    - static_configs:
        - targets:
          # - alertmanager:9093

# Load rules once and periodically evaluate them according to the global 'evaluation_interval'.
rule_files:

# A scrape configuration containing exactly one endpoint to scrape:
# Here it's Prometheus itself.
scrape_configs:
  - job_name: bookmark
    scrape_interval: 10s
    static_configs:
      - targets: ["host.docker.internal:8080"]
  - job_name: pushgateway
    scrape_interval: 10s
    static_configs:
      - targets: ["host.docker.internal:9091"]
```

Mais detalhes sobre as configurações do Prometheus podem ser vistas na [documentação oficial](https://prometheus.io/docs/introduction/overview/).

Ao executarmos o comando `docker-compose up -d` podemos ver os serviços sendo executados:

```
docker-compose up -d
Starting bookmark-pushgateway         ... done
Starting bookmark-mongodb             ... done
Starting clean-architecture-go_node_1 ... done
Starting bookmark-prometheus          ... done
Starting bookmark-grafana             ... done
```

### Configurando o Grafana

Vamos agora usar o Grafana para criarmos as visualizações dos dados coletados pelo Prometheus.

Ao acessar o link `http://localhost:3000/login` é preciso fazer o login inicial com o usuário `admin` e a senha `admin` (e gerar uma nova senha, conforme solicitado pela interface).

Após o login é preciso criar um novo `data source`, usando a opção na interface. Ao selecionar a opção `Prometheus` é necessário preencher com as informações:

[![datasource](/images/posts/datasource.png)](/images/posts/datasource.png)

Na opção `Dashboards` precisamos importar os dashboards padrão:

[![datasource_dashboard](/images/posts/datasource_dashboard.png)](/images/posts/datasource_dashboard.png)

Vamos agora criar nosso primeiro dashboard:

[![dashboard](/images/posts/dashboard.png)](/images/posts/dashboard.png)

Selecionando a opção `Add query` vamos preencher com os dados:

[![dashboard_dados](/images/posts/dashboard_dados.png)](/images/posts/dashboard_dados.png)

No campo da query adicionamos:

    http_request_duration_seconds_count{job="bookmark"} > 0

E no campo `Legend` colocamos as informações que queremos mostrar:

    {{handler}} - {{method}} - {{code}}

Desta forma vamos visualizar também qual é o método e o status code, além da URL acessada.

Na opção General vamos dar um nome para nossa visualização:

[![dashboard_title](/images/posts/dashboard_title.png)](/images/posts/dashboard_title.png)

Como não vamos criar alertas neste momento, podemos clicar na opção de voltar, no topo da página, para visualizarmos nosso dashboard atualizado.

Vamos agora adicionar um novo painel, com as informações do nosso CLI:

[![new_panel](/images/posts/new_panel.png)](/images/posts/new_panel.png)

E vamos criar uma nova query:

[![dashboard_cli](/images/posts/dashboard_cli.png)](/images/posts/dashboard_cli.png)

Na query colocamos o valor:

    pushgateway_cmd_duration_seconds_sum

E como legenda usamos:

    {{name}}

Podemos dar um nome para o nosso novo painel, na opção General e voltarmos ao dashboard, que agora ficou desta forma:

[![dashboard_final](/images/posts/dashboard_final.png)](/images/posts/dashboard_final.png)

Conforme as métricas vão sendo coletadas os dados vão ser atualizados no dashboard. É possível adicionar outros painéis, com queries mais avançadas e outras coletas. Na documentação do Prometheus e do Grafana existem exemplos mais avançados.

## Conclusão

Neste post meu objetivo foi demonstrar como é relativamente simples adicionar a feature de métricas a aplicações Go. Um ponto extra é o fato de estarmos usando a Clean Architecture, o que nos permite mudarmos do Prometheus para outra solução bastando criar uma nova implementação da interface `metric.UseCase` e alterar poucas linhas de configuração. Estas métricas tem nos ajudado a entender melhor o comportamento da nossa aplicação e facilitado algumas decisões de implementação e melhorias. Espero ter ajudado para que mais projetos também tenham estes benefícios.

Os códigos apresentados neste post encontram-se no repositório [https://github.com/eminetto/clean-architecture-go](https://github.com/eminetto/clean-architecture-go)
