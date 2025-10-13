---
categories: null
comments: true
date: 2015-03-18T00:00:00Z
title: Usando o Deployer
url: /2015/03/18/usando-o-deployer/
---

Segundo a Wikipedia, "software deployment" significa: 

> "todas as atividades necessárias para tornar um software disponível para uso"

Esta lista de atividades pode ser algo simples como enviar um diretório de arquivos para um FTP ou um processo complexo, envolvendo migrações de banco de dados, múltiplos servidores, vários níveis de cache, etc. 

Geralmente o processo de deployment automatizado é uma sequencia natural do processo de integração contínua e exitem diversas ferramentas e formas de se automatizar esse processo, tanto gratuitas quanto pagas.

Vamos considerar os dois extremos de complexidade no quesito deploy automatizado. De um lado o mais simples: um shell script que realiza uma série de comandos Linux (ou outro sistema operacional) para colocar o software em produção. De outro lado um sistema de deploy ligado a uma ferramenta de integração contínua como o Jenkins, Codeship ou Travis. 

Em dois projetos de clientes da Coderockr resolvemos adotar uma solução intermediária: scripts PHP. Para isso estamos usando o [Deployer](http://deployer.org/).

O primeiro passo que fiz foi criar um diretório chamado _deploy_ dentro da estrutura do projeto e dentro dele salvar o [_deployer.phar_](http://deployer.org/deployer.phar). Também é possível instalar via Composer ou fazer um clone do projeto, mas achei o _.phar_ uma solução mais fácil de se usar. 

O próximo passo é criar um arquivo chamado _deploy.php_ que vai conter as atividades necessárias para o projeto. 

O exemplo abaixo é baseado no script de um dos projetos:

``` php
<?php

//como vamos usar o .pem não é necessário o ssh
set('use_ssh2', false);
$project_path = '/var/www/html/project';

/* OBS: Os .pem estão salvos dentro do diretório deploy 
e vão para o Git do projeto*/

//servidor de homologação. 
server('homolog', 'homolog.project.com')
  ->path('/var/www/html/project')
  ->user('ubuntu')
  ->pemFile('homolog.pem');

//servidor de produção
server('production', 'production.project.com')
  ->path('/var/www/html/project')
  ->user('ubuntu')
  ->pemFile('production.pem');

//subtaks
task('update_git', function ($input) {
    $branch = $input->getOption('branch', get('branch', null));
    run("sudo git pull origin $branch");
})->option('branch', 'b', 'Escolha a branch para deploy', 'master')
  ->desc('Atualiza os códigos no servidor');

task('run_fixtures', function ($input) use ($project_path) {
    cd($project_path . '/Backend');
    run("sudo php fixtures.php");
})->option('branch', 'b', 'Escolha a branch para deploy', 'master')
  ->desc('Roda as fixtures');

task('run_frontend_scripts', function ($input) use ($project_path) {
    cd($project_path . '/Frontend');
    run("sudo rm -rf ../Backend/public/app");
    run("sudo bower cache clean --allow-root");
    run("sudo bower install --force --allow-root");
    run("sudo grunt --force");
})->option('branch', 'b', 'Escolha a branch para deploy', 'master')
  ->desc('Roda os scripts de frontend');

task('fix_permissions', function ($input) {
    run("sudo chown -R www-data:www-data /tmp/__CG__*");
})->option('branch', 'b', 'Escolha a branch para deploy', 'master')
  ->desc('Concerta as permissões');

//tasks principais
task('run', array(
  'update_git',
  'run_fixtures',
  'run_frontend_scripts',
  'fix_permissions'
))->option('branch', 'b', 'Escolha a branch para deploy', 'master')
  ->desc('Deploy para servidor');

```

Podemos verificar se está tudo funcionando rodando o comando que vai listar as opções de deploy:

	cd deploy
	php deployer.phar list

E você deve ver algo parecido com isso:

```
Deployer version 2.0.2

Usage:
 [options] command [arguments]

Options:
 --help (-h)           Display this help message
 --quiet (-q)          Do not output any message
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version
 --ansi                Force ANSI output
 --no-ansi             Disable ANSI output
 --no-interaction (-n) Do not ask any interactive question

Available commands:
 fix_permissions        Concerta as permissões
 help                   Displays help for a command
 run                    Deploy para servidor
 list                   Lists commands
 run_fixtures           Roda as fixtures
 run_frontend_scripts   Roda os scripts de frontend
 self-update            Updates deployer.phar to the latest version
 update_git             Atualiza os códigos no servidor

```

Para executar o deploy basta usar o comando:

	php deployer.phar run --server=homolog -vvv

Com o _--server=homolog_ estamos indicando qual o servidor a ser  usado e com o _-vvv_ estamos solicitando que os passos sejam mostrados detalhadamente na tela. 

No comando acima é usada a branch _master_ para os comandos do Git, mas podemos indicar outra com o parâmetro programado no script:

	php deployer.phar run -b fix_errors --server=homolog -vvv

Sendo _fix\_errors_ um exemplo de nome de _branch_.

Da mesma forma podemos usar o comando para enviar os dados para nosso servidor de produção:

	php deployer.phar run --server=production -vvv

Como o _deploy.php_ é um script PHP as possibilidades são enormes. Podemos usar variáveis de ambiente, componentes de frameworks, realizar consultas a bancos de dados, usar a SDK da Amazon para criar novas máquinas e realizar o deploy para elas,  etc. 

Como comentei no começo do post, existem diversas [outras formas](http://eltonminetto.net/blog/2013/11/11/deploy-estilo-heroku-usando-git/) de realizarmos esse processo, mas o Deployer me impressionou pela sua facilidade de uso e versatilidade. 