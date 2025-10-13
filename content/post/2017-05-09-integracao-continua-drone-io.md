+++
bigimg = ""
date = "2017-05-09T01:42:24+02:00"
subtitle = ""
title = "Integração contínua usando o Drone"

+++

Integração contínua e deploy contínuo são dois pontos muito importantes no "[Coderockr Way](https://www.youtube.com/playlist?list=PLkS5lYehKysZhw1prsoZQhiVfbYA5fEGz)", o processo de desenvolvimento que usamos na [Coderockr](http://coderockr.com). Neste post vou falar sobre uma nova ferramenta que estamos avaliando, o Drone.

<!--more-->

O [Drone](https://drone.io/) é uma solução open source, desenvolvida em Go e baseada fortemente no uso de containers Docker. Ele é formado por três componentes principais:

- **Server**: faz a orquestração dos builds, ouvindo eventos em repositórios hospedados no Github, Gitlab ou Bitbucket e gerenciando a execução usando os agentes. Ele é um container Docker que deve ser executado em um local que possa ser visível tanto pelos agentes quanto pela hospedagem do repositório.
- **Agents**: os agentes fazem o trabalho pesado do processo. São eles que recebem do server as instruções que devem ser executadas durante um build ou deploy. São containers Docker que podem ser executados em qualquer máquina que suporte containers.
- **Cli**: o aplicativo de linha de comando é opcional mas ajuda bastante no processo de configuração e gerenciamento de deploys.

Vamos começar instalando o servidor e um agente em uma máquina. Para isso basta criar um arquivo docker-compose.yml como o abaixo:

```
version: '2'

services:
  drone-server:
    image: drone/drone:0.6
    ports:
      - 8001:8000
    volumes:
      - ./:/var/lib/drone/
    restart: always
    environment:
      - DRONE_OPEN=true
      - DRONE_GITHUB=true
      - DRONE_GITHUB_CLIENT=AQUI_VAI_O_APP_ID
      - DRONE_GITHUB_SECRET=AQUI_VAI_O_APP_SECRET
      - DRONE_SECRET=nashaijhsaihsaiua
      - DRONE_ADMIN=eminetto
      - DRONE_GITHUB_SCOPE=repo,repo:status,user:email,read:org
      - DRONE_GITHUB_MERGE_REF=true
      - DRONE_GITHUB_SKIP_VERIFY=true
  drone-agent:
    image: drone/drone:0.6
    command: agent
    restart: always
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
    environment:
      - DRONE_SERVER=ws://ci.seu_server.com:8001/ws/broker
      - DRONE_SECRET=nashaijhsaihsaiua

```

Neste exemplo vamos usar a integração com o Github para executar os testes. O primeiro passo é criarmos uma integração do Github com o Drone e para isso é preciso acessar a URL: https://github.com/settings/applications/new e registrar uma nova aplicação. 

- O campo nome é usado para a sua organização, então pode ser algo como Drone. 
- No campo *Homepage URL* coloque o endereço do seu servidor: *http://ci.seu_server.com:8001*. Note que é o mesmo endereço usado na variável DRONE_SERVER do docker-compose.yml. 
- No campo *Authorization callback URL* complete com o seguinte conteúdo: *http://ci.seu_server.com:8001/authorize*. 

Após confirmar a criação o Github vai mostrar as informações de *Client ID* e *Client Secret* que devem ser salvas no docker-compose.yml, nas variáveis *DRONE_GITHUB_CLIENT* e *DRONE_GITHUB_SECRET*. Ainda sobre este arquivo, a variável *DRONE_SECRET* é uma informação que deve ser igual entre o server e os agents, para garantir a segurança. 

Com tudo configurado basta executar o comando

	docker-compose up -d

E acessar, via navegador o *http://ci.seu_server.com:8001*. Na interface é possível fazer login com o usuário do Github e visualizar os projetos e builds cadastrados. No momento ainda não temos nenhum projeto, então este vai ser o próximo passo. 

Para facilitar este exemplo vou criar um repositório com um projeto do Zend Expressive, pois este já configura um ambiente de testes simples. Para isso usei os comandos:

	curl -s http://getcomposer.org/installer | php
	php composer.phar create-project zendframework/zend-expressive-skeleton drone-post
	
Após responder os valores padrão que o script pergunta podemos verificar se os testes estão passando, usando os comandos:
	
	cd drone-post/
	./vendor/bin/phpunit
	
Precisamos configurar o repositório para que os testes sejam executados a cada push. O primeiro passo é criarmos no repositório um arquivo chamado .drone.yml, que vai definir os passos que os agentes devem executar. O conteúdo deste arquivo ficou da seguinte forma:

```
pipeline:
  test:
    image: php:7.1-cli
    commands:
      - apt-get update       
      - apt-get install git -y
      - curl --silent --show-error https://getcomposer.org/installer | php
      - php composer.phar install
      - ./vendor/bin/phpunit --coverage-text --colors=never
```

Em *image* indicamos qual é o container Docker que vai ser usado para executar os *commands*. Neste caso estou usando o container oficial do PHP, que pode ser encontrado no [Docker Hub](https://hub.docker.com/_/php/). É possível usar qualquer container que esteja no Docker Hub ou em outro registro, tanto público quanto privado. Na documentação do Drone é possível ver como usar estes casos mais complexos.

O último passo é fazer a integração entre o Github e o Drone, para que o servidor seja avisado a cada alteração no repositório e inicialize os builds. É possível fazer isso usando a interface do Drone via navegador, ou usando o aplicativo de linha de comando. Vamos usar o CLI para fazer este processo. 

Após [instalá-lo](http://docs.drone.io/cli-installation/) em seu computador é preciso configurá-lo para que possa acessar o seu servidor. Para isso é preciso configurar duas variáveis de ambiente:

	export DRONE_SERVER="http://ci.seu_server.com:8001"
	export DRONE_TOKEN="token"

Este token pode ser encontrado no seu servidor, na URL: *http://ci.seu_server.com:8001/account*, na opção *Show token*. 

Agora basta executarmos:

	 drone repo add eminetto/drone-post

Para que isto seja possível é necessário que o usuário dono do Token seja um administrador do Drone, o que foi configurado na variável *DRONE_ADMIN* do docker-compose.yml. O comando acima criou um *Webhook* no repositório do Github indicando que os eventos de push, commit, deploy, etc, devem ser enviados ao nosso servidor do Drone. Agora basta fazer push dos arquivos para o repositório e acessar a interface do Drone para ver os testes passando:

[![drone](/images/posts/drone.png)](/images/posts/drone.png) 

O que é interessante na arquitetura do Drone é que basta executar mais agentes, em mais máquinas, que o processo de desenvolvimento escala conforme sua necessidade. É possível, por exemplo, usar as máquinas dos desenvolvedores para executarem builds, aumentando a velocidade durante o horário de trabalho. 

Neste primeiro post apresentei o básico do Drone. Em futuros textos vou falar sobre deploy, plugins, segurança, etc. Enquanto isso é possível acessar a [documentação oficial](http://docs.drone.io/) para encontrar mais detalhes e exemplos avançados. 