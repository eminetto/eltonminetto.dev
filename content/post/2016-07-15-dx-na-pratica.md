+++
bigimg = ""
date = "2016-07-15T08:48:06-03:00"
subtitle = "Stripe X Paypal"
title = "Developer Experience na prática"

+++
No [post anterior](http://eltonminetto.net/2016/06/16/dx-developer-experience/) eu falei sobre a ideia do DX e como implementar algo similar em nossas empresas. Neste post quero comentar um exemplo prático comparando duas empresas similares.

<!--more-->

Quando o Stripe foi lançado nos EUA o mercado de gateways de pagamento já estava consolidado, com grandes players como o Paypal. Nestes cenários a única forma de uma nova empresa se destacar é criando uma "vantagem competitiva" em relação aos outros concorrentes. E a escolha do Stripe para esta vantagem foi exatamente ser voltada aos desenvolvedores. 

A diferença começa pelos sites dos dois concorrentes. Navegando pelo site do Paypal é possível ver que ele é focado em quem quer vender e comprar usando a ferramenta. 

Um exemplo banal. Veja o número de cliques que eu precisei fazer no site do Paypal até encontrar alguma documentação técnica e exemplos de código:

{{< youtube ORbHtmFwu7Q >}}

Agora compare o mesmo procedimento no site do Stripe:

{{< youtube IIT8mDAuQ0Q >}}

Estou a apenas um clique de um exemplo funcional de código no site do Stripe! 

A API do Paypal não é ruim, mas é confusa. Existem diversas opções, diversos sites e formas de acessar a mesma coisa. A documentação do Stripe é muito mais rápida e prática:

[![stripe](/images/posts/stripe.png)](/images/posts/stripe.png) 

Nesta mesma tela eu posso ver exemplos de implementação em diversas linguagens de programação. O Paypal também tem algo parecido:

[![stripe](/images/posts/paypal.png)](/images/posts/paypal.png) 

mas ao selecionar uma linguagem eu sou redirecionado a uma página do Github com um exemplo. A forma como o Stripe faz é muito mais simples. 

Quanto ao Github ambas as empresas estão de parabéns por manterem diversos exemplos e bibliotecas como open source. Novamente o Stripe ganha alguns pontos extras por usar alguns padrões e linguagens mais novas, mas é muito bom poder ter acesso a códigos e poder colaborar com os mesmos. 

Claro que uma decisão como "selecionar o gateway de pagamentos da minha empresa" leva em conta mais itens do que apenas a experiência do desenvolvedor, mas isso é importante. Todos os clientes da [Coderockr](http://coderockr.com) que passaram por esta decisão nos consultaram sobre as empresas e nossa opinião foi crucial na escolha. E eu levei isso em consideração ao escolher o Stripe para o sistema de assinaturas do [Planrockr](http://planrockr.com).

Além do exemplo dos gateways de pagamento outros casos similares podem ser apontados, como a Digital Ocean, o Slack e a brasileira Umbler. Todas são reconhecidas por inovarem em seus negócios ao focar em facilitar o trabalho dos desenvolvedores. Fica a dica para empresas que querem destacar-se em suas áreas. 

Você lembra de mais algum exemplo parecido? Não concorda com a minha visão? Por favor contribua com seus exemplos e opiniões nos comentários.