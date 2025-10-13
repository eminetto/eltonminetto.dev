---
categories:
- python
comments: true
date: 2011-12-09T15:14:49Z
slug: backup-dos-e-mails-do-gmail
title: Backup dos e-mails do Gmail
url: /2011/12/09/backup-dos-e-mails-do-gmail/
wordpress_id: 841
---

Hoje precisei fazer uma tarefa que acabou me levando a descobrir uma nova ferramenta. A tarefa em questão era: "exportar para uma lista os e-mails de todas as pessoas que compraram o meu [e-book](http://www.zfnapratica.com.br)". Toda venda realizada com sucesso o Pagseguro me envia um e-mail avisando da venda, e um script PHP que eu criei envia o PDF do e-book para o e-mail do comprador. Então teoricamente só precisaria exportar essa lista de e-mails enviados da minha conta do Gmail para um arquivo TXT. 
Após pesquisar algumas alternativas cheguei ao [Got Your Back (GYB)](http://code.google.com/p/got-your-back/). É um script Python que serve para fazer um backup/restore de todos os e-mails da sua conta do Gmail. Ele salva todas as mensagens em pastas separadas por ano/mes, no formato .eml, o que facilita o uso de alguma ferramenta de busca de textos como o find do MacOSX/Linux. E para facilitar ainda mais ele gera um banco de dados SQLite com os detalhes da mensagem (from, to, subject), então é só fazer uma consulta SQL e você tem acesso a todas as suas mensagens. 
Achei bem útil e fácil de usar, então resolvi compartilhar.
