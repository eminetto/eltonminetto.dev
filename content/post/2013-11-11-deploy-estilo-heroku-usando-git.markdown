---
categories:
- devops
comments: true
date: 2013-11-11T00:00:00Z
title: Deploy estilo Heroku usando Git
url: /2013/11/11/deploy-estilo-heroku-usando-git/
---

Venho estudando bastante sobre _DevOps_ recentemente, inclusive vou apresentar uma das [keynotes](http://phpconference.com.br/presentations/show/id/73) do PHP Conference Brasil 2013 no final de Novembro. 

E um dos pontos importantes de todo o processo de desenvolvimento é o deploy. Na [Coderockr](http://coderockr.com) estamos usando o seguinte ambiente:

- [Vagrant](http://code-squad.com/screencast/vagrant) para facilitar a configuração das máquinas dos desenvolvedores
- Github para armazenar os repositórios de códigos
- Amazon AWS (e começamos a usar Azure recentemente) para os servidores

A ideia da cultura _DevOps_ e suas ferramentas é diminuir a barreira entre o desenvolvimento e o deploy, deixando a vida mais fácil tanto para desenvolvedores quanto gerentes de rede/servidores/operações. 

Então pensamos em uma forma de unificar este nosso ambiente e tornar o deploy das aplicações mais rápido. Com o plugin [vagrant-aws](https://github.com/mitchellh/vagrant-aws) basta um simples comando (_vagrant up --provider=aws_) para que uma máquina seja criada na Amazon, com o ambiente configurado, exatamente igual ao ambiente que usamos para desenvolver a aplicação. 

O próximo passo é facilitar o deploy das novas versões e correções do software. Sempre achei muito interessante a abordagem que o [Heroku](https://www.heroku.com) (e outras plataformas de PaaS) vem usando, de permitir o deploy apenas com um _git push_. Então fiz algumas [pesquisas](http://jesseditson.com/setting-up-heroku-like-git-push-deployment) para tentar emular este funcionamento na nossa arquitetura.

O primeiro requisito é que você possa fazer login via ssh no seu servidor, preferencialmente sem senha usando chaves privadas. Neste [post](http://www.vivaolinux.com.br/artigo/SSH-Conexao-sem-senha) você pode ver como configurar isto no seu servidor. 

O próximo passo é conectar no servidor e configurar o repositório remoto e o diretório do seu aplicativo. Neste exemplo estou usando um Ubuntu Server com uma aplicação PHP, mas isto deve funcionar para qualquer ambiente/linguagem de programação. 

	ssh ubuntu@ip_do_servidor
	mkdir -p repositorios/minha_app
	cd repositorios/minha_app
	git init --bare

Este diretório vai conter apenas a estrutura que o git vai usar para receber os arquivos da aplicação. Vamos criar um diretório para a aplicação, neste caso no _/var/www_ onde o servidor Apache está configurado para procurar a nossa aplicação PHP. Também vamos dar permissão de escrita neste diretório para o usuário _ubuntu_:

	sudo mkdir /var/www/minha_app
	sudo chown -R ubuntu:ubuntu /var/www/minha_app

Vamos agora configurar o gatilho (ou gancho se você for traduzir a palavra _hook_ literalmente) que o git vai executar após receber o push. 
Para isso vamos criar o arquivo 

	repositorios/minha_app/hooks/post-receive

E neste arquivo podemos colocar qualquer script que precise ser executado após o deploy. As primeiras linhas do script devem ter algo como:

	#!/bin/bash
	export GIT_WORK_TREE=/var/www/minha_app
	git checkout -f

A primeira linha indica que é um shell script bash. A segunda é importante pois indica ao git que todos os comandos a serem executados devem ser feitos neste caminho. A terceira linha é opcional pois neste exemplo ela vai descartar qualquer alteração que tenha sido feita nos arquivos deste diretório e usar os novos arquivos sendo enviados via push (cuidado com esta linha, pois se o seu aplicativo criou algum arquivo neste diretório ele será descartado).

Abaixo um exemplo de script de uma aplicação que desenvolvemos na Coderockr, usando Silex e Doctrine:

	#!/bin/bash
	export GIT_WORK_TREE=/var/www/minha_app
	git checkout -f
	cd /var/www/minha_app
	php composer.phar install
	./vendor/bin/doctrine orm:schema-tool:update --force
	sudo service apache2 reload
	
O script atualiza os pacotes usando o [Composer](http://code-squad.com/screencast/composer) e faz a atualização das entidade do [Doctrine](http://leanpub.com/doctrine-na-pratica), além de fazer um reload no Apache. 

Não esqueça de dar permissão de execução para o script executando o comando:

	chmod +x repositorios/minha_app/hooks/post-receive

O último passo é adicionar o repositório na sua aplicação (na sua máquina de desenvolvimento): 

	git remote add production ssh://ubuntu@ip_do_servidor/home/ubuntu/repositorios/minha_app

Depois basta fazer push para este novo repositório remoto e a mágica é executada:

	git push production master

	Counting objects: 9, done.
	Delta compression using up to 4 threads.
	Compressing objects: 100% (5/5), done.
	Writing objects: 100% (5/5), 431 bytes | 0 bytes/s, done.
	Total 5 (delta 4), reused 0 (delta 0)
	remote: Loading composer repositories with package information
	remote: Installing dependencies (including require-dev) from lock file
	remote:   - Installing symfony/event-dispatcher (2.3.x-dev 2d8ece3)
	remote:     Cloning 2d8ece3c610726a73d0c95c885134efea182610e
	remote:
	remote:   - Installing guzzle/guzzle (dev-master 4363c95)
	remote:     Cloning 4363c95b584dcafc8ff386c27116283ad53d738d
	remote:
	remote:   - Installing aws/aws-sdk-php (dev-master 80ec29d)
	remote:     Cloning 80ec29d00a23471553005e6948609304aeb4e899
	remote:
	remote:   - Installing symfony/routing (2.3.x-dev 7d41463)
	remote:     Cloning 7d41463094752e87a0fae60316d236abecb8a034
	.....
	remote:  * Reloading web server apache2
	remote:  *

Este é só um exemplo das facilidades que algumas ferramentas podem fornecer ao nosso dia a dia de desenvolvedores. Se estiver em São Paulo no final de Novembro e puder participar do PHP Conference Brasil 2013 eu vou estar por lá para trocarmos ideias sobre PHP e sobre DevOps ;)