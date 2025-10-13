---
title: "Monitorando uma aplicação Golang com o Supervisor"
subtitle: ""
date: "2018-11-28T10:54:24+02:00"
bigimg: ""
tags:
  - go
---

Leitor amigo... Se você estiver lendo este post alguns anos depois da sua publicação, lembre-se que em 2018 estávamos alucinados com "novidades" como microsserviços, Docker, Kubernetes, entre outras coisas legais.

<!--more-->

Então a primeira reação que temos ao pensar no assunto "deploy de aplicações" é querer colocar tudo isso em prática e executar a API, ou uma mão cheia de microsserviços, em um ambiente complexo, com Kubernetes, Istio, etc. Eu sei porque passei por isso nas primeiras semanas na Code:Nation...

Mas para estes momentos é sempre bom buscar a sabedoria da internet:

[![XhK8RJv](/images/posts/XhK8RJv.jpg)](/images/posts/XhK8RJv.jpg)

Pensando nisso, neste post vou apresentar uma solução bem mais simples e que deve servir para um grande número de projetos. Trata-se do [Supervisor](http://supervisord.org), uma ferramenta que faz o monitoramento de processos em ambientes Unix.

A primeira vez que usei o Supervisor foi em meados de 2008, para gerenciar consumidores de filas de processos (na época usando o _Gearman_) escritos em PHP. Isto reforça duas coisas:

1. estou ficando velho;
2. o Supervisor é uma ferramenta que tem bastante experiência no mercado.

Vamos ao exemplo.

## Instalação

Para este exemplo eu usei um Ubuntu 18.04, mas no site oficial é possível encontrar documentação sobre o processo de instalação em outras distribuições.

No Ubuntu executei:

    sudo apt-get update
    sudo apt-get install -y supervisor
    sudo service supervisor start

Podemos usar o comando abaixo para verificar os serviços monitorados:

    sudo supervisorctl status

No momento não temos nenhum serviço monitorado, então a saída do comando é vazia.

Vamos criar um exemplo de API em Go (versão 1.11), criando um `main.go`:

[![main](/images/posts/main.png)](/images/posts/main.png)

Vamos gerar o binário:

    sudo go build -o /usr/local/bin/api main.go

O próximo passo é criar a configuração do primeiro serviço monitorado. Para isso criamos o arquivo abaixo:

    sudo vim /etc/supervisor/conf.d/api.conf

Com o conteúdo:

    [program:api]
    directory=/usr/local
    command=/usr/local/bin/api
    autostart=true
    autorestart=true
    stderr_logfile=/var/log/api.err
    stdout_logfile=/var/log/api.log
    environment=CODENATION_ENV=prod

Para cada processo que o Supervisor gerenciar é necessário criar um arquivo nos moldes acima. Nele configuramos o nome único do processo (`[program:api]`), qual é o comando que vai ser executado (`command=/usr/local/bin/api`), se o Supervisor deve reiniciá-lo caso algum erro aconteça (`autorestart=true`) e o destino dos logs (`stderr_logfile` e `stdout_logfile`). Também podemos configurar variáveis de ambiente que serão usadas pelo comando (`environment`) e outras opções mais avançadas que podem ser encontradas na documentação.

Precisamos agora fazer com que o Supervisor releia as configurações, incluindo o novo arquivo criado:

    ubuntu@7648e3e0ef2b:~ sudo supervisorctl reload
    Restarted supervisord

Podemos agora ver o status do aplicativo:

    root@759cc81a91f0:~ sudo supervisorctl status
    api            RUNNING   pid 3032, uptime 0:00:03

Podemos ver que o processo está executando, qual é seu `pid` e o seu `uptime`.

Vamos fazer uma alteração na API para que ela gere log dos acessos. Para isso vamos alterar nosso `main.go`:

[![main_stdout](/images/posts/main_stdout.png)](/images/posts/main_stdout.png)

Para atualizar o binário basta executar os comandos:

    sudo supervisorctl stop api
    sudo go build -o /usr/local/bin/api main.go
    sudo supervisorctl start api

Após acessarmos a API algumas vezes podemos ver o conteúdo do log no arquivo `/var/log/api.log`, conforme configurado no `/etc/supervisor/conf.d/api.conf`:

```
cat /var/log/api.log
2018/11/28 23:22:12 main.go:28: 127.0.0.1:42282 GET /
2018/11/28 23:22:13 main.go:28: 127.0.0.1:42284 GET /
2018/11/28 23:22:14 main.go:28: 127.0.0.1:42286 GET /
2018/11/28 23:22:14 main.go:28: 127.0.0.1:42288 GET /
2018/11/28 23:22:14 main.go:28: 127.0.0.1:42290 GET /
2018/11/28 23:22:15 main.go:28: 127.0.0.1:42292 GET /
2018/11/28 23:22:17 main.go:28: 127.0.0.1:42294 GET /
```

Vamos agora alterar nossa API para que ela possa gerar registro tanto dos acessos quanto dos erros. Para isso vamos criar uma URL que simula um erro:

[![main_stderr](/images/posts/main_stderr.png)](/images/posts/main_stderr.png)

Vamos atualizar novamente o binário. Podemos executar:

    sudo go build -o /usr/local/bin/api main.go
    sudo supervisorctl restart api

Assim o Supervisor vai reiniciar a nossa API. Vamos agora simular um acesso à URL problemática:

    root@759cc81a91f0:~ curl http://localhost:8080/erro
    curl: (52) Empty reply from server

Podemos ver o registro do erro no arquivo de log correspondente:

    root@759cc81a91f0:~ cat /var/log/api.err
    ERROR 2018/11/28 23:42:29 main.go:29: Something wrong happened

Como o Supervisor está gerenciando o processo, ao perceber que o programa foi interrompido ele automaticamente o reiniciou, como podemos ver com o comando:

    root@759cc81a91f0:~ supervisorctl status
    api          RUNNING   pid 3857, uptime 0:00:22

Desta forma garantimos que o serviço continua sendo executado, além de termos logs do que aconteceu.

Na [Code:Nation](https://www.codenation.com.br) estamos usando o Supervisor em conjunto com o [drone](http://drone.io) para automatizarmos o processo de deploy dos nossos serviços e o resultado tem se mostrado muito eficaz. É uma solução bem mais leve do que a alternativa usando containers, Kubernetes, e etc. Assim podemos nos preocupar menos com infraestrutura no momento e focar mais no desenvolvimento do produto, algo muito importante para startups. Conforme formos crescendo, tanto em número de usuários como de complexidade do produto, podemos substituir esta solução por algo mais robusto.

Acredito que este cenário seja comum a várias empresas e espero ter ajudado compartilhando nossa experiência.
