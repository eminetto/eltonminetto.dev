+++
title = "Integração contínua em projetos usando monorepo"
subtitle = ""
date = "2018-08-01T10:54:24+02:00"
bigimg = ""
+++

Iniciar qualquer projeto de software envolve a tomada de uma série de decisões. Dentre as decisões corretas que tomamos no projeto da [Code:Nation](https://www.codenation.com.br) eu posso citar a escolha da linguagem Go em conjunto com a [Clean Architecture](https://medium.com/@eminetto/clean-architecture-using-golang-b63587aa5e3f), a recente adoção dos conceitos de [JAMstack](https://deploy.codenation.com.br/jamstack-na-code-nation-d31223f8165e) e a escolha por organizarmos o código na forma de um único repositório no Github. Neste post quero falar especialmente sobre este último ponto, o _monorepo_ e como resolvemos um dos desafios desta decisão. 

<!--more-->

Com o aumento da complexidade dos projetos, com microserviços e diferentes interfaces consumindo recursos, surgiu a discussão entre separar o código em diversos repositórios ou unificar tudo em um único. Talvez os maiores exemplos de empresas que adotaram o monorepo são o Google e a DigitalOcean, conforme podemos ver nos posts:

- [Why Google Stores Billions of Lines of Code in a Single Repository](https://cacm.acm.org/magazines/2016/7/204032-why-google-stores-billions-of-lines-of-code-in-a-single-repository/fulltext)
- [Cthulhu: Organizing Go Code in a Scalable Repo](https://blog.digitalocean.com/cthulhu-organizing-go-code-in-a-scalable-repo/)

Na minha opinião as maiores vantagens desta abordagem é simplificar o gerenciamento do repositório e facilitar o reaproveitamento de código, algo que fica ainda mais fácil graças a Clean Architecture.

Mas um dos desafios do _monorepo_ é o processo de _build_ e _deploy_ automatizados. Com todo o projeto na mesma estrutura é preciso  cuidado ao definir as estratégias de CI/CD para evitar que o tempo de _build/deploy_ não torne-se uma pedra no caminho da equipe. No post da DigitalOcean que citei acima eles comentam que resolveram este problema criando uma ferramenta interna chamada _gta_(_Go Test Auto_), que infelizmente não tornaram open source. Para resolver este problema usei uma abordagem similar a citada pela DigitalOcean, mas usando shell script. 

Atualmente esta é a estrutura de diretórios do nosso repositório:

```bash
api = API and documentation
chatbots - telegram, facebook and slack chatbots
cli = codenation cli, used by developers to run the challenges
cmd = utils and fixtures
core = Go core packages, used by all the project
docs = source code of internal docs (hosted at Github Pages)
frontend = Vue.js project and templates used by Sam
infra = configuration files used by staging and production servers
lambda = lambda functions
research = Python notebooks and other research assets
sam = cli tool used by us to generate pages, include challenges and other admin tasks
scripts = shell scrips used by CI/CD and other admin tasks
web = ReactJS project (Signin, Signup, Forgot password) - IN PROCESS OF DEPRECATION
workers = workers that consume SQS queues
.drone.yml = CI/CD configuration file
.goreleaser.yml = Goreleaser configuration file. Used to deploy the codenation-cli to Github, Homebrew
docker-compose.yml = Docker configuration used by local and staging environments
Gopkg.* = Go dependencies configuration files
Makefile = build and admin tasks
```

Nós usamos o [Drone](http://drone.io) para gerenciar nosso processo de _build/deploy_.  Venho usando ele [desde 2017](https://eltonminetto.net/post/2017-05-09-integracao-continua-drone-io/) e ele tem se mostrado outra decisão correta. Nosso _pipeline_ de _build_ pode ser visto na imagem:

[![pipeline](/images/posts/drone_pipeline.png)](/images/posts/drone_pipeline.png) 

E um trecho do nosso arquivo de configuração do Drone pode ser visto abaixo:

[![config](/images/posts/drone_config.png)](/images/posts/drone_config.png) 

Como é possível ver no arquivo de configuração, o passo _golang-build-api_ executa o script *drone_go_build_api.sh*. Neste script temos o seguinte código:

```bash
#!/bin/bash -e
watch="api core"
. scripts/shouldIBuild.sh
shouldIBuild
if [[ $SHOULD_BUILD = 0 ]]; then
    exit 0
fi
make linux-binaries-api
BUILD_EXIT_STATUS=$?
exit $BUILD_EXIT_STATUS
```

A variável _watch_ contém a lista de diretórios que devem ser monitoradas para definir se este passo deve ou não ser executado. Esta decisão é tomada no script _shouldIBuild.sh_: 

```bash
#!/bin/bash -e
SHOULD_BUILD=0
shouldIBuild() {
    if [[ "${DRONE_DEPLOY_TO}" ]]; then 
        SHOULD_BUILD=1
    else
        . scripts/detectChangedFolders.sh
        detect_changed_folders
        toW=($(echo "$watch" | tr ' ' '\n'))
        changed=($(echo "$changed_components"))
        for i in "${toW[@]}"
        do
            for j in "${changed[@]}"
            do
                if [[ $i = $j ]]; then
                SHOULD_BUILD=1 
                fi
            done
        done
    fi
}
```

O primeiro teste realizado verifica se está sendo executado um _deploy_, identificado pela variável de ambiente *DRONE_DEPLOY_TO*. Caso positivo a variável *SHOULD_BUILD* é definida como verdadeira (1) e o passo deve ser executado. Caso não seja um _deploy_ o script _detectChangedFolders.sh_ é usado para verificar se um dos diretórios definidos em _watch_ está sendo alterado neste _build_. Caso seja positivo o passo vai ser executado. O código do _detectChangedFolders.sh_ é:

```bash
#!/bin/bash -e
export IGNORE_FILES=$(ls -p | grep -v /)

detect_changed_folders() {
    if [[ "${DRONE_PULL_REQUEST}" ]]; then 
        folders=$(git --no-pager diff --name-only FETCH_HEAD FETCH_HEAD~1 | sort -u | awk 'BEGIN {FS="/"} {print $1}' | uniq); 
    else 
        folders=$(git --no-pager diff --name-only HEAD~1 | sort -u | awk 'BEGIN {FS="/"} {print $1}' | uniq); 
    fi
    export changed_components=$folders
}
```

A mesma configuração é feita em todos os scripts usados pelo Drone. Assim, uma alteração no diretório _frontend_, por exemplo, não vai gerar o _build_ da _api_ ou dos _workers_ de filas. 

Com esta mudança conseguimos diminuir nosso tempo de _build_ de mais de 5 minutos para alguns segundos, dependendo do que estiver sendo alterado no _commit_ realizado pelo desenvolvedor.

Acredito que seja fácil alterar os scripts e adaptar a solução para outras ferramentas de integração contínua. Espero que esta dica ajude quem estiver tendo problemas similares. 