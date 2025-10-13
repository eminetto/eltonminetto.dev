---
categories:
- php
comments: true
date: 2012-04-02T21:38:49Z
slug: usando-o-vagrant-para-criar-maquinas-virtuais-para-desenvolvimento-e-testes
title: Usando Vagrant para criar máquinas virtuais para desenvolvimento e testes
url: /2012/04/02/usando-o-vagrant-para-criar-maquinas-virtuais-para-desenvolvimento-e-testes/
wordpress_id: 934
---

Recentemente trabalhei em um projeto grande, com equipes trabalhando remotamente, cada desenvolvedor usando o seu ambiente favorito para trabalhar (Linux, MacOS X e Windows) e o sistema tendo diversos requisitos (PHP, MySQL, Memcached, Solr, PHPUnit, etc). Era comum acontecerem problemas como "na minha máquina todos os testes funcionam, mas na máquina de fulano, que roda Windows não" ou "temos um novo programador na equipe, precisamos instalar todos os requisitos para ele começar a trabalhar". Para resolver este tipo de problemas uma solução é usarmos máquinas virtuais, com todos os requisitos já instalados e prontos para uso. O VMWare e o VirtualBox são exemplos interessantes para estes casos, mas ainda assim exigem um pouco de trabalho para configurar e instalar tudo. O [Vagrant](http://vagrantup.com/) é uma ferramenta que auxilia exatamente neste quesito, a criação das máquinas virtuais.
O Vagrant roda em Windows, Linux e MacOS X (onde eu fiz os testes que apresento nesse post) e necessita do VirtualBox para funcionar. Neste post vou descrever os passos que fizemos na Coderockr para criarmos máquinas virtuais Ubuntu dentro de nossos MacOS X.
O primeiro passo é fazer o download do Vagrant no link [http://downloads.vagrantup.com/tags/v1.0.2](http://downloads.vagrantup.com/tags/v1.0.2)
Depois precisamos fazer o download da máquina virtual "base" que será usada para gerar as máquinas para cada projeto, com o comando

```
vagrant box add base http://files.vagrantup.com/lucid32.box
```

Um arquivo de 260M é copiado para o diretório .vagrant.d de seu home (/Users/eminetto/.vagrant.d no meu caso). É uma imagem do VirtualBox com o sistema Ubuntu 10.04.

Agora vamos criar o nosso primeiro projeto. Eu criei um diretório:

```
mkdir ~/Projects/vagrant
```

E dentro deste diretório devemos executar o comando

```
vagrant init
```

É criado um arquivo chamado Vagrantfile que é a configuração da sua máquina virtual
Vamos alterar o arquivo e alterar a linha abaixo, que indica qual é nossa VM original.

``` ruby
config.vm.box = "base"
```

Se inicializarmos a máquina neste momento ela será criada com o sistema Ubuntu "zerado", sem nenhum pacote adicional, o que não é muito útil para nossa necessidade. Vamos usar uma ferramenta chamada [Puppet](http://puppetlabs.com/) (também é possível usar a ferramenta [Chef](http://vagrantup.com/docs/provisioners/chef_solo.html)) para automatizar o processo de instalação dos pacotes necessários.
Para instalar o Puppet é necessário o interpretador da linguagem Ruby, que já vem instalado no MacOS X e na maioria dos sistemas Linux atuais (ou pode ser instalado usando o apt-get ou yum, dependendo da distribuição). Vamos executar o comando:

```
sudo gem install puppet
```

Agora precisamos criar um arquivo de configuração para o Puppet. No diretório do projeto (~/Projects/vagrant) vamos criar o diretório manifests:

```
mkdir manifests
```

e o arquivo manifests/base.pp, cujo conteúdo está no link [https://gist.github.com/2288198](https://gist.github.com/2288198)
Neste arquivo definimos os comandos que queremos executar (exec), os pacotes que devem ser instalados (package) e os serviços que devem ser inicializados (service).
Precisamos também configurar o arquivo Vagrantfile para que ele execute o Puppet:

``` ruby
config.vm.provision :puppet do |puppet|
    puppet.manifests_path = "manifests"
    puppet.manifest_file  = "base.pp"
end
```

Agora basta criar a máquina virtual, com o comando

```
vagrant up
```

A primeira vez deve demorar alguns minutos, pois a máquina "base" é clonada e o Puppet é executado para instalar os pacotes que indicamos

Para acessar o Apache instalado na máquina virtual é só acessar a url http://127.0.0.1:8080 e para acessar o SSH basta executar

```
vagrant ssh
```

Quando precisar desligar a máquina é só executar

```
vagrant halt
```

e para inicializar novamente basta um

```
vagrant up
```

Caso queira remover a máquina e recriá-la o comando é

```
vagrant destroy
```

E repetir o processo anterior.

Também é possível compartilhar a máquina criada com o restante da equipe, como mostra a [documentação oficial](http://vagrantup.com/docs/getting-started/packaging.html)

O Vagrant facilita bastante o processo de criação do ambiente de desenvolvimento, e trás diversas vantagens, tanto para um programador solo (poder separar o ambiente de desenvolvimento da máquina real, ter vários ambientes distintos, para os diversos projetos) quanto para equipes (poder facilmente instalar novas máquinas e ter o mesmo ambiente de desenvolvimento em todas as máquinas da equipe).
