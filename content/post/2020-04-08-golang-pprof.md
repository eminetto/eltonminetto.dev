---
title: "Fazendo profiling de aplicações Golang usando pprof"
subtitle: ""
date: "2020-04-08T08:33:24+02:00"
bigimg: ""
tags:
  - go
---

Nas últimas semanas a [Codenation](https://codenation.dev) vem passando por um crescimento bem significativo. Confirmando a frase do grande [Bruno Ghisi](https://www.linkedin.com/in/brunoghisi/) que fala que "na escala tudo quebra", funcionalidades que sempre funcionavam perfeitamente começam a tornar-se problemáticas.

Começamos a observar nas métricas do nosso [Prometheus](https://eltonminetto.dev/post/2020-03-12-golang-prometheus/) que um dos _endpoints_ da nossa API estava consumindo muitos recursos. Conversando com a equipe chegamos a um motivo suspeito, mas antes de começar a refatorar o código achei melhor fazer uma análise mais "científica". Como nossa API foi desenvolvida em Go, famosa por sua ótima coleção de ferramentas nativas, iniciei a análise usando a solução "oficial", o [pprof](https://blog.golang.org/pprof).

Como o foco da análise era um _endpoint_ específico, fiz uma alteração simples no código da aplicação:

```go
func privateJourneyFindAll(services *core.Services) http.Handler {
   return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
      f, err := os.Create("privateJourneyFindAll.prof")
      if err != nil {
         log.Fatal(err)
      }
      pprof.StartCPUProfile(f)
      defer pprof.StopCPUProfile()
      //restante do código, inalterado
```

Esta alteração faz com que seja criado um arquivo com a coleta de dados que será analisado posteriormente. Estou analisando apenas o consumo de CPU, que era o problema principal neste caso, mas o _pprof_ permite coletar mais métricas, conforme pode ser visto na documentação do pacote.

Após a alteração basta fazer a compilação normal:

    go build -o ./bin/api api/main.go

Ao acessar o _endpoint_ usando a interface ou o `curl` o arquivo `privateJourneyFindAll.prof` vai ser criado. Com ele podemos fazer a análise, executando o comando:

    go tool pprof bin/api privateJourneyFindAll.prof

É necessário indicar a localização do binário e também do arquivo de dados. Um _prompt_ vai ser apresentado:

    File: api
    Type: cpu
    Time: Apr 8, 2020 at 8:38am (-03)
    Duration: 660.56ms, Total samples = 90ms (13.62%)
    Entering interactive mode (type "help" for commands, "o" for options)
    (pprof)

Neste _prompt_ podemos executar alguns comandos, como:

    (pprof) top10
    Showing nodes accounting for 90ms, 100% of 90ms total
    Showing top 10 nodes out of 72
      flat  flat%   sum%        cum   cum%
      20ms 22.22% 22.22%       40ms 44.44%  runtime.mallocgc
      20ms 22.22% 44.44%       20ms 22.22%  runtime.memclrNoHeapPointers
      10ms 11.11% 55.56%       10ms 11.11%  reflect.Value.assignTo
      10ms 11.11% 66.67%       10ms 11.11%  runtime.madvise
      10ms 11.11% 77.78%       10ms 11.11%  runtime.memmove
      10ms 11.11% 88.89%       10ms 11.11%  runtime.pthread_cond_wait
      10ms 11.11%   100%       10ms 11.11%  syscall.syscall
         0     0%   100%       20ms 22.22%  github.com/codegangsta/negroni.(*Logger).ServeHTTP
         0     0%   100%       20ms 22.22%  github.com/codegangsta/negroni.(*Negroni).ServeHTTP
         0     0%   100%       20ms 22.22%  github.com/codegangsta/negroni.HandlerFunc.ServeHTTP
    (pprof)

Existem outros comandos, mas o mais interessante é o:

    (pprof) web
    (pprof)

Este comando vai abrir o seu navegador padrão com uma visualização em forma de árvore da execução da sua aplicação, como no exemplo abaixo. **OBS**: eu fiz um recorte da imagem original, para focar apenas na parte importante para este post, mas a visualização geralmente é bem grande e vale a pena ser analisada com atenção.

[![pprof001_before](/images/posts/pprof001_before.png)](/images/posts/pprof001_before.png)

Os dois pontos mais importantes na imagem são a duração de **1.11 segundos** e o caminho em vermelho formado pelas funções _ListAsTalent_ e _IsCompleted_, que são o gargalo do _endpoint_, comprovando a suspeita inicial da equipe.

Após passar algumas horas de refatoração e testes gerei novamente o profiling com a versão atualizada, e o resultado foi bem mais satisfatório:

[![pprof002_after](/images/posts/pprof002_after.png)](/images/posts/pprof002_after.png)

Agora o tempo caiu para **301.35 milisegundos** e a árvore está bem melhor balanceada, sem caminhos críticos como anteriormente.

**OBS**: antes de fazer o _commit_ da alteração eu removi os códigos referentes a coleta de dados para a geração do profiling. No post sobre o _pprof_ que citei no começo deste texto é possível ver técnicas mais avançadas para ativar/desativar esse recurso, usando flags e parâmetros.

Graças ao profiling gerado pelo _pprof_ foi muito mais rápido encontrar o gargalo da aplicação, nos permitindo focar exatamente onde estava o problema.
