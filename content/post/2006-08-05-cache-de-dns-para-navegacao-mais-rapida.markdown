---
categories:
- home
- ubuntu
comments: true
date: 2006-08-05T11:01:09Z
slug: cache-de-dns-para-navegacao-mais-rapida
title: None
url: /2006/08/05/cache-de-dns-para-navegacao-mais-rapida/
wordpress_id: 158
---

Lendo o rss [deste blog](http://ubuntu.wordpress.com/) encontrei um post interessante sobre como instalar a ferramenta dnsmasq para fazer cache de DNS na máquina local, aumentando a velocidade da navegação. A velocidade é aumentada porque cada vez que é digitado um endereço no navegador ou outra aplicação, este endereço é convertido para seu endereço IP. Com o dnsmasq um cache é criado com estas informações. Depois que você já usou o endereço uma vez é bem mais rápido esta conversão porque as informações estão locais. Instalei no meu Dapper e a diferença foi visível. A minha conexão com a Internet é através de uma  ADSL. Minha máquina recebe as configurações de rede do modem ADSL via DHCP. Traduzindo as informações do blog:

Para instalar ``dnsmasq é só:

sudo apt-get install dnsmasq

Depois é preciso configurá-lo. Para isto deve-se editar o arquivo:

sudo gedit /etc/dnsmasq.conf

Deve-se procurar a linha que tem o conteúdo

#listen-address =

e alterar para

listen-address=127.0.0.1

Lembrando que este é o endereço IP da máquina local.

Como citei, minha máquina recebe as informações de IP via DHCP, então é preciso configurar o arquivo:

sudo gedit /etc/dhcp3/dhclient.conf

Deve-se procurar a linha:

#prepend domain-name-servers 127.0.0.1;

e remover o comentário, ficando:

prepend domain-name-servers 127.0.0.1;

Desta maneira o cliente do dhcp vai manter a máquina local como servidor de nomes  sempre. Depois é preciso alterar o arquivo /etc/resolv.conf para adicionar a máquina local para ser um servidor de nomes.  Então:

sudo gedit /etc/resolv.conf

O meu arquivo estava assim:

search dummy.net
nameserver 192.168.200.254

e depois de alterado:

search dummy.net
nameserver 127.0.0.1
nameserver 192.168.200.254
Estas informações serão diferentes em cada máquina, pois esta informação é recebida do seu DHCP ou é configurada manualmente. O que foi adicionado foi a linha nameserver 127.0.0.1

Depois é preciso reiniciar o dnsmasq para ele reconhecer as alterações de configuração:

sudo /etc/init.d/dnsmasq restart

Para testar é só navegar ou executar o comando dig. Exemplo. Executando o comando

dig ubuntu.org
pela primeira vez ele levou 300 msec para encontrar o endereço IP. Nas próximas vezes isso caiu para 35 msec.
No [site original](http://ubuntu.wordpress.com/2006/08/02/local-dns-cache-for-faster-browsing/) pode-se encontrar mais alguns exemplos que não testei porque não refletem meu ambiente.

``
