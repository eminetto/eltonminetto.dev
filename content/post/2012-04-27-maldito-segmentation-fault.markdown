---
categories:
- coderockr
- codes
comments: true
date: 2012-04-27T15:30:50Z
slug: maldito-segmentation-fault
title: Maldito segmentation fault
url: /2012/04/27/maldito-segmentation-fault/
wordpress_id: 968
---

Esse é um daqueles posts que serve mais para me ajudar, mas acho que pode ser útil para mais pessoas.
Já tive vários casos onde uma aplicação PHP gera um erro muito genérico nos logs do Apache, o famigerado "Segmentation fault". Na prática significa que alguma coisa deu tão errado que o processo do Apache que estava executando o seu script PHP foi cancelado. Mas o log não ajuda muito, pois podem ser várias coisas.
Nesse post vou descrever o processo que fiz para encontrar um problema em uma aplicação. Os passos foram feitos em uma máquina virtual Ubuntu, a que eu gerei com o [Vagrant](/blog/2012/04/02/usando-o-vagrant-para-criar-maquinas-virtuais-para-desenvolvimento-e-testes/).
O primeiro passo é instalar os pacotes necessários para conseguirmos toda a informação possível:

```
apt-get install libapr1-dbg libaprutil1-dbg gdb php5-dbg
```

Precisamos também configurar o Apache para que ele gere um "dump" com toda a informação do erro em um arquivo. No arquivo /etc/apache2/apache2.conf eu adicionei a seguinte linha:

```
CoreDumpDirectory /tmp/apache2-gdb-dump
```

Também precisamos criar o diretório e dar permissões para o Apache criar os arquivos:

```
mkdir /tmp/apache2-gdb-dump
chown -R www-data:www-data /tmp/apache2-gdb-dump
```

Após reiniciar o Apache é só executar novamente o script que está gerando o erro, e um arquivo será gerado no diretório criado, o _/tmp/apache2-gdb-dump/core_

Com esse arquivo podemos executar o gdb e verificar o que aconteceu com o programa. Precisamos executar:

```
gdb /usr/sbin/apache2 /tmp/apache2-gdb-dump/core
```

Será mostrado uma lista de ítens que o Apache executou. Para ver mais detalhe é só executar o comando 

```
bt full
```

Analisando as mensagem é bem provável que você vai encontrar uma pista para o que exatamente está ocorrendo. No caso da minha aplicação que testei hoje o problema era na forma como estava carregando o arquivo .phar do Silex, conforme o que o gdb me mostrou:

```
#0  0x0112cefe in phar_get_archive (archive=0xbff5f87c, fname=0xb5dab6c4 "/vagrant/SOA-Server/vendor/silex.phar", fname_len=55, alias=0x213f323c "silex.phar", alias_len=10, error=0x0) at /build/buildd/php5-5.3.2/ext/phar/util.c:1255
```

Espero que esse post seja útil para mais alguém, e se você tiver outra receita de como desvendar esse tipo de problemas é só usar os comentários para dar a dica.

