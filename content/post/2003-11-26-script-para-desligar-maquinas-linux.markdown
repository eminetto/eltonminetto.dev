---
categories:
- codes
- python
comments: true
date: 2003-11-26T19:32:42Z
slug: script-para-desligar-maquinas-linux
title: None
url: /2003/11/26/script-para-desligar-maquinas-linux/
wordpress_id: 26
---

A idéia destes dois scripts é criar uma forma fácil e segura de, a partir de um servidor poder desligar outras máquinas.
Foi criado para suprir uma necessidade bem específica minha. Um dos meus servidors Linux fica conectado a um nobreak gerenciável, via conexão serial. Quando a carga deste nobreak está chegando ao fim ele avisa este servidor, que envia um comando via rede para os outros servidores que são desligados antes que a energia acabe.
Nos servidores que serão desligados, o script servidor_shutdown.py é executado na inicialização, da seguinte forma:
servidor_shutdown.py x.x.x.x onde x.x.x.x é o endereço IP do servidor ligado ao nobreak. Somente através deste endereço IP é permitido o desligamento da máquina.Quando este comando é executado, o script fica "ouvindo" em uma porta TCP, a 50007.
Quando o nobreak avisa à máquina que a energia está acabando, esta executa o comando:
cliente_shutdown.py z.z.z.z onde z.z.z.z é o endereço IP da máquina q será desligada. O script envia um comando para a porta 50007 da máquina a ser desligada, que verifica se o IP e o comando recebido são os permitidos e desliga-se.
Utilizando regras de firewall para controlar o acesso a esta porta TCP apenas ao endereço IP permitido é possível desligar todos os servidores de uma maneira segura.

Downloads

[servidor_shutdown.py](/codes/servidor_shutdown.py)
[cliente_shutdown.py](/codes/cliente_shutdown.py)
