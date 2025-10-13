---
categories:
- codes
- home
- mysql
comments: true
date: 2010-01-28T20:38:19Z
slug: enviando-dms-do-twitter-via-shell-script
title: Enviando DMs do Twitter via shell script
url: /2010/01/28/enviando-dms-do-twitter-via-shell-script/
wordpress_id: 407
---

Monitorar os servidores e serviços é uma tarefa importante para manter seus sites e sistemas sempre em ordem. Existem diversas ferramentas bem completas como o Nagios que fazem praticamente todo o serviço. Eu uso o Nagios para monitorar quase tudo e ele me avisa via e-mail de quaisquer problemas. O problema é que nã verifico e-mail o tempo todo.  Uma solução seria mandar os avisos via SMS, mas isso envolve alguns custos.

Nesse momento me lembrei do Twitter. Eu sou um daqueles viciados, que verifica o twitter diversas vezes por dia.  Pensando nisso fiz um pequeno script para monitorar o MySQL e caso o ping não responda eu recebo uma DM avisando.

Um exemplo do script:

    
    status=`mysqladmin ping -hhost -uuser -psenha 2> /dev/null`
    if [ "$status"  != "mysqld is alive" ]; then
       curl -u usuario_mon:senha -d "text=Erro conectando ao MySQL&user=eminetto" http://twitter.com/direct_messages/new.xml 2> /dev/null > /dev/null
    fi




Um detalhe importante a lembrar é que não é bom usar sua conta no Twitter para enviar as DMs. O Twitter tem uma polí­tica que remove contas que usam muitas DMs por dia, temendo abuso e SPAM. O melhor é criar uma nova conta.  Além disso, é preciso que as duas contas sejam seguidoras uma da outra, senão a DM não pode ser enviada




Claro que eu poderia criar um plugin ou script para o próprio Nagios e deixar ele fazer isso para mim, mas assim ficou mais rápido. Além disso é só um exemplo




Fonte: [http://davidwalsh.name/twitter-dm](http://davidwalsh.name/twitter-dm)
