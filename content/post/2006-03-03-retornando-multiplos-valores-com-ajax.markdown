---
categories:
- home
comments: true
date: 2006-03-03T15:05:28Z
slug: retornando-multiplos-valores-com-ajax
title: None
url: /2006/03/03/retornando-multiplos-valores-com-ajax/
wordpress_id: 115
---

Algumas pessoas me mandaram e-mails pedindo como fazer para retornar múltiplos valores em uma requisição AJAX.

Eu havia resolvido de uma maneira não muito elegante. O PHP gerava uma string separada por vírgulas e no Javascript eu fazia o split da string para separar os campos e os valores. Pesquisando na Internet eu encontrei nesse [site](http://zilbo.com/articles/ajax_how.html) uma maneira um pouco mais elegante. Fiz um exemplo para testar e ficou realmente bem mais fácil de trabalhar.

**Código da página HTML**

[ Código](/codes/showphp.php?file=multiplo.php)

**Código do script PHP
**

[ Código](/codes/showphp.php?file=multiplo_ajax.php)
