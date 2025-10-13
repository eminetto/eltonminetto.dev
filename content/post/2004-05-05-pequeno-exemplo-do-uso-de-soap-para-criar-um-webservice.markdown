---
categories:
- codes
comments: true
date: 2004-05-05T19:21:10Z
slug: pequeno-exemplo-do-uso-de-soap-para-criar-um-webservice
title: Pequeno exemplo do uso de SOAP para criar um webservice
url: /2004/05/05/pequeno-exemplo-do-uso-de-soap-para-criar-um-webservice/
wordpress_id: 22
---

Segundo [Mauro Sant'Anna](mailto:mas_mauro@hotmail.com) em [http://www.linhadecodigo.com.br/](http://www.linhadecodigo.com.br/),
"
O SOAP é um protocolo elaborado para facilitar a chamada remota de funções via Internet, permitindo que dois programas se comuniquem de uma maneira tecnicamente muito semelhante à invocação de páginas Web.

O protocolo SOAP tem diversas vantagens sobre outras maneiras de chamar funções remotamente como DCOM, CORBA ou diretamente no TCP/IP:
* É simples de implementar, testar e usar.
* É um padrão da indústria, criado por um consórcio , adotado pela W3C (http://www.w3.org/TR/SOAP/) e por várias outras empresas.
* Usa os mesmos padrões da Web para quase tudo: a comunicação é feita via HTTP com pacotes virtualmente idênticos; os protocolos de autenticação e encriptação são os mesmos; a manutenção de estado é feita da mesma forma; é normalmente implementado pelo próprio servidor Web.
* Atravessa firewalls e roteadores, que pensam que é uma comunicação HTTP.
* Tanto os dados como as funções são descritas em XML, o que torna o protocolo não apenas fácil de usar como também muito robusto.
* É independente do sistema operacional e CPU.
* Pode ser usado tanto de forma anônima como com autenticação (nome/senha).
"

Neste exemplo utilizei o SOAPpy, uma implementação do SOAP que faz parte do projeto pywebsvcs, encontrada em http://pywebsvcs.sourceforge.net/ Fiz uma pequena calculadora para ilustrar a utilização do SOAPpy para criar um webservice e como desenvolver um cliente que faça uso da sua funcionalidade. Como é citado acima, o cliente poderia ser desenvolvido em qualquer linguagem de programação e plataforma.
Com isto dá pra ter uma idéia das possibilidades que os webservices fornecem.

[CalcSOAP.py](/codes/CalcSOAP.py)

[ClientCalcSOAP.py](/codes/ClientCalcSOAP.py)
