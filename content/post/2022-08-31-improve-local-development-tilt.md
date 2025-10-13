---
title: "Acelere seu ambiente de desenvolvimento local com o Tilt"
date: 2022-08-31T13:00:19-03:00
draft: false
tags:
  - go
---

Passamos horas e horas desenvolvendo aplicações nas nossas máquinas, cada vez com mais requisitos e complexidade. Qualquer aplicação moderna facilmente conta com vários containers, microsserviços, deploys em diferentes ambientes, diversas stacks, etc. Então qualquer ferramenta que possa tornar nosso fluxo mais ágil é de grande utilidade.

Neste post quero apresentar uma ferramenta muito poderosa, que pode fazê-lo economizar bastante tempo no seu processo de desenvolvimento. Trata-se do [Tilt](https://tilt.dev), que recentemente foi [adquirida](https://www.docker.com/blog/welcome-tilt-fixing-the-pains-of-microservice-development-for-kubernetes/) pela Docker.

Para demonstrar um pouco do que é possível fazer com o Tilt eu vou usar este meu [repositório](https://github.com/eminetto/talk-microservices-go) que usei em uma [palestra](https://eltonminetto/dev/files/talks/presentation-190914144745.pdf) sobre microsserviços. Os exemplos vão ser feitos em Go, mas na documentação oficial é possível visualizar como usá-lo com [outras tecnologias](https://docs.tilt.dev/example_static_html.html) e cenários.

## Instalação

O primeiro passo é instalar o aplicativo de linha de comando. Para isso, no meu macOS eu executei:

```bash
curl -fsSL https://raw.githubusercontent.com/tilt-dev/tilt/master/scripts/install.sh | bash
```

Na [documentação](https://docs.tilt.dev/install.html) é possível ver como fazer a instalação em outros sistemas operacionais.

## Primeiros passos

O Tilt funciona lendo um arquivo chamado `Tiltfile` na raiz do seu projeto. Ele possui uma sintaxe que lembra bastante `Python` e a [documentação](https://docs.tilt.dev/api.html) é bem detalhada, mostrando todas as opções que podemos configurar.

O conteúdo do arquivo `Tiltfile` ficou desta forma:

```python
local_resource('auth', cmd='cd auth; go build -o bin/auth main.go',
               serve_cmd='auth/bin/auth', deps=['auth/main.go', 'auth/security', 'auth/user', 'pkg'])

local_resource('feedbacks', cmd='cd feedbacks; go build -o bin/feedbacks main.go',
               serve_cmd='feedbacks/bin/feedbacks', deps=['feedbacks/main.go', 'feedbacks/feedback', 'pkg'])


local_resource('votes', cmd='cd votes; go build -o bin/votes main.go',
               serve_cmd='votes/bin/votes', deps=['votes/main.go', 'votes/vote', 'pkg'])

```

A função `local_resource` configura comandos que vão ser executados na sua máquina local. O primeiro parâmetro é o nome que estamos dando para o recurso, que deve ser único dentro do `Tiltfile`. O parâmetro `cmd` contém o comando que vai ser executado. O comando contido dentro do parâmetro `serve_cmd` vai ser executado pelo Tilt e é esperado que ele não termine. Ou seja, é o comando que vai executar nosso serviço.

O último parâmetro, o `deps` é um dos mais interessantes. Ele indica quais diretórios do projeto o Tilt vai observar, e se acontecerem alterações ele vai automaticamente executar o processo. Isso significa que se acontecer qualquer alteração em `auth/main.go`, `auth/security`, `auth/user`, ou `pkg` o serviço `auth` vai ser recompilado e executado novamente. Tratando-se de uma linguagem compilada como Go, isso é uma grande ajuda porque basta alterar o arquivo e ele já vai ser automaticamente gerado.

Como o nosso projeto é composto por três microsserviços, o restante do `Tiltfile` configura o mesmo comportamento para todos.

Para executar o Tilt basta abrir um terminal e digitar:

```bash
tilt up
```

A seguinte ela é apresentada:

[![tilt](/images/posts/tilt.png)](/images/posts/tilt.png)

Pressionando a barra de espaço é nos apresentada a interface gráfica do Tilt, onde vamos passar bastante tempo:

[![tilt](/images/posts/tilt_ui.png)](/images/posts/tilt_ui.png)

Nesta tela podemos verificar o log de compilação de cada aplicação, bem como executar novamente o passo desejado. Ela também agrega os logs da aplicação, e nos permite fazer pesquisas neles:

[![tilt](/images/posts/tilt_ui_log.png)](/images/posts/tilt_ui_log.png)

Erros de compilação também aparecem nessa tela:

[![tilt](/images/posts/tilt_ui_error.png)](/images/posts/tilt_ui_error.png)

Somente estas funcionalidades que apresentei até agora já devem ser o suficiente para colocar o Tilt na sua lista de ferramentas a testar, certo? Mas vamos aprofundar um pouco mais.

## Containers

Vamos agora aprimorar nosso ambiente. Ao invés de executarmos os binários localmente, vamos adicionar o recurso da criação e atualização automática de containers dos nossos microsserviços. Afinal, eles devem ser executados desta forma no ambiente de produção.

A nova versão do `Tiltfile` ficou assim:

```python
local_resource(
    'auth-compile',
    cmd='cd auth; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/auth main.go',
    deps=['auth/main.go', 'auth/security', 'auth/user', 'pkg'],
)

docker_build(
    'auth-image',
    './auth',
    dockerfile='auth/Dockerfile',
)

local_resource(
    'feedbacks-compile',
    cmd='cd feedbacks; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/feedbacks main.go',
    deps=['feedbacks/main.go', 'feedbacks/feedback', 'pkg'],
)

docker_build(
    'feedbacks-image',
    './feedbacks',
    dockerfile='feedbacks/Dockerfile',
)

local_resource(
    'votes-compile',
    cmd='cd votes; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/votes main.go',
    deps=['votes/main.go', 'votes/vote', 'pkg'],
)

docker_build(
    'votes-image',
    './votes',
    dockerfile='votes/Dockerfile',
)

docker_compose('./docker-compose.yml')
```

Adicionei a função `docker_build` que, como o nome sugere, faz a geração da imagem do container. Para isso foi preciso criar um `Dockerfile` para cada microsserviço. Por exemplo, a do serviço `auth` ficou desta forma:

```yaml
FROM alpine
ADD bin/auth /
EXPOSE 8081
CMD ["/auth"]
```

A dos demais serviços ficou bem semelhante, só alterando o nome do executável e da porta: `feedbacks` roda na porta `8082` e `votes` na `8083`.

Ao fazer essa alteração o Tilt vai avisar que é necessário ter alguma forma de deploy dos containers, senão ele não vai funcionar. Uma forma de se fazer isso é criando um `docker-compose.yml` e usá-lo na função `docker_compose`. Seu conteúdo ficou assim:

```yaml
version: "3"
services:
  auth:
    image: auth-image
    ports:
      - "8081:8081"
    container_name: auth
  feedbacks:
    image: feedbacks-image
    ports:
      - "8082:8082"
    container_name: feedbacks
  votes:
    image: votes-image
    ports:
      - "8083:8083"
    container_name: votes
```

Uma última alteração no código foi necessária. No [trecho de código](https://github.com/eminetto/talk-microservices-go/blob/master/pkg/middleware/is_authenticated.go#L25) que faz a comunicação entre os serviços, foi preciso alterar o endereço do serviço `auth`.

O novo conteúdo ficou:

```go
//@TODO get address from environment variables
req, err := http.Post("http://auth:8081/v1/validate-token", "text/plain", strings.NewReader(payload))
```

Observação: isso só foi preciso fazer porque o endereço estava fixo no código e o correto era estar em uma variável de ambiente, como é possível ver no `@TODO` que nunca foi implementado :D

Com estas alterações o Tilt agora observa mudanças nos códigos do projeto e caso aconteçam ele faz a compilação, geração dos conteiners e atualização do ambiente!

[![tilt](/images/posts/tilt_ui_docker.png)](/images/posts/tilt_ui_docker.png)

## Kubernetes!!

Agora vamos tornar a coisa um pouco mais séria! Vamos fazer com que o Tilt faça o deploy da nossa aplicação em um cluster Kubernetes. Para isso vou usar o [minikube](https://minikube.sigs.k8s.io/docs/), solução que faz a instalação de um cluster local para desenvolvimento.

No macOS bastou executar:

```bash
brew install minikube
```

E depois de instalado foi só executar o comando:

```bash
minikube start
```

Agora que temos nosso cluster configurado vamos alterar o nosso `Tiltfile` para refletir o novo ambiente:

```python
load('ext://restart_process', 'docker_build_with_restart')
local_resource(
    'auth-compile',
    cmd='cd auth; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/auth main.go',
    deps=['auth/main.go', 'auth/security', 'auth/user', 'pkg'],
)

docker_build_with_restart(
    'auth-image',
    './auth',
    dockerfile='auth/Dockerfile',
    entrypoint=['/auth'],
    live_update=[
        sync('./auth/bin/auth', '/auth'),
    ],
)

k8s_yaml('auth/kubernetes.yaml')
k8s_resource('ms-auth', port_forwards=8081,
             resource_deps=['auth-compile'])


local_resource(
    'feedbacks-compile',
    cmd='cd feedbacks; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/feedbacks main.go',
    deps=['feedbacks/main.go', 'feedbacks/feedback', 'pkg'],
)

docker_build_with_restart(
    'feedbacks-image',
    './feedbacks',
    dockerfile='feedbacks/Dockerfile',
    entrypoint=['/feedbacks'],
    live_update=[
        sync('./feedbacks/bin/feedbacks', '/feedbacks'),
    ],
)

k8s_yaml('feedbacks/kubernetes.yaml')
k8s_resource('ms-feedbacks', port_forwards=8082,
             resource_deps=['feedbacks-compile'])


local_resource(
    'votes-compile',
    cmd='cd votes; CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o bin/votes main.go',
    deps=['votes/main.go', 'votes/vote', 'pkg'],
)

docker_build_with_restart(
    'votes-image',
    './votes',
    dockerfile='votes/Dockerfile',
    entrypoint=['/votes'],
    live_update=[
        sync('./votes/bin/votes', '/votes'),
    ],
)

k8s_yaml('votes/kubernetes.yaml')
k8s_resource('ms-votes', port_forwards=8083,
             resource_deps=['votes-compile'])

```

Aqui temos um bom número de novidades.

A primeira é a função `load` que faz o carregamento de [extensions](https://docs.tilt.dev/extensions.html) do Tilt. É uma forma de expandir as funcionalidades da ferramenta, e existem [várias](https://github.com/tilt-dev/tilt-extensions) disponíveis. Aqui estamos usando a `docker_build_with_restart` que vai fazer o trabalho de atualizar o container executando dentro do nosso cluster Kubernetes.

Outra mudança é relacionada às configurações do deploy das aplicações dentro do Kubernetes. A função `k8s_yaml` indica qual é o arquivo que contém a "receita" que vai ser usada para o deploy. E a função `k8s_resource` é usada aqui para fazer o redirecionamento da porta do cluster para o nosso ambiente local, facilitando os testes.

O conteúdo do arquivo `auth/kubernetes.yaml` é:

```yaml
apiVersion: v1
kind: Service
metadata:
  labels:
    app: ms-auth
  name: ms-auth
spec:
  ports:
    - port: 8081
      name: http
      protocol: TCP
      targetPort: 8081
  selector:
    app: ms-auth
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: ms-auth
  labels:
    app: ms-auth
spec:
  selector:
    matchLabels:
      app: ms-auth
  template:
    metadata:
      labels:
        app: ms-auth
    spec:
      containers:
        - name: ms-auth
          image: auth-image
          ports:
            - containerPort: 8081
```

Os demais arquivos são praticamente iguais, somente mudando o nome do binário e da porta.

Agora o Tilt faz todo o trabalho pesado para nós:

[![tilt](/images/posts/tilt_ui_k8s.png)](/images/posts/tilt_ui_k8s.png)

Para conferir se nossos microsserviços estão sendo executados no cluster podemos usar o comando:

```bash
kubectl get pods -n default
NAME                          READY   STATUS    RESTARTS   AGE
ms-auth-7446897869-89r2j      1/1     Running   0          81s
ms-feedbacks-b5df67d6-wzbj2   1/1     Running   0          81s
ms-votes-76565ddc9c-nkkt7     1/1     Running   0          81s
```

## Conclusões

Não sei se consegui com esse post demonstrar o quão empolgado estou com essa ferramenta! Venho usando o Tilt fazem algumas semanas em um projeto bem complexo, a criação de um [Controller](https://kubernetes.io/docs/concepts/architecture/controller/) para o Kubernetes e graças a toda essa automação eu posso me concentrar apenas na lógica da aplicação, enquanto que o restante é feito automaticamente. E isso economiza muito tempo.

Um agradecimento ao meu colega [Felipe Paes de Oliveira](https://www.linkedin.com/in/felipewebcloud/) que me apresentou essa incrível ferramenta. E se você quer ver o Tilt sendo demonstrado pela grande [Ellen Korbes](http://ellenkorbes.com), que trabalha lá, confere nesse [vídeo](https://www.youtube.com/watch?v=itzm_ZNN74s).
