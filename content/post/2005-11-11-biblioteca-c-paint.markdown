---
categories:
- codes
- docs
- home
comments: true
date: 2005-11-11T17:04:02Z
slug: biblioteca-c-paint
title: Biblioteca C-Paint
url: /2005/11/11/biblioteca-c-paint/
wordpress_id: 90
---

Lendo a edição 63 da revista [www.com.br](http://www.europanet.com.br/euro2003/index.php?cat_id=3) tomei conhecimento de uma biblioteca chamada [C-Paint](http://cpaint.booleansystems.com/). Segundo uma livre tradução do site:
"CPAINT (Cross-Platform Asynchronous INterface Toolkit)  é um toolkit multi-linguagens que ajuda os desenvolvedores web a implementar aplicações AJAX com facilidade e flexibilidade. Ela nasceu da frustração e desapontamento do autor com outros toolkits open-sorce para AJAX.  É construído nos mesmos princípios do AJAX, usando JavaScript e objetos XMLHTTP no lado do cliente e uma linguagem de script apropriada no lado do servidor, para completar o círculo de envio dos dados do cliente para o servidor e vice-versa."
Como é citado acima, do lado do servidor pode-se usar tanto PHP quanto ASP, o que confirma a flexibilidade desejada.
Fiz alguns testes e achei o código resultante muito mais limpo que usando-se o SAJAX, por exemplo. Além de ser bem mais simples de entender e programar. Refiz o [exemplo](/?p=70) que tinha feito antes com o SAJAX para demonstrar.

**Código do "Cliente"**

[Este é o código na página html](/codes/showphp.php?file=cpaint.php)

**Código do "Servidor"**

[Este é o código do script PHP que será executado](/codes/showphp.php?file=funcoes_ajax.php)

É um toolkit realmente interessante. Eu estou substituindo os códigos que fiz com o SAJAX pelo C-Paint. O legal é unir no funcoes_ajax.php todas as funções que podem ser usadas pelo sistema, assim reaproveitando código e centralizando a manutenção nesse arquivo.
Fica aí essa dica de ferramenta.
