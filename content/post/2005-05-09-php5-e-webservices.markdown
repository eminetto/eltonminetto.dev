---
categories:
- codes
- docs
- home
comments: true
date: 2005-05-09T23:06:02Z
slug: php5-e-webservices
title: PHP5 e Webservices
url: /2005/05/09/php5-e-webservices/
wordpress_id: 60
---

Hoje consegui um pouco de tempo para dar uma pesquisada e aprender mais um pouco sobre webservices. Resolvi ver como funciona o suporte nativo a SOAP embutido no PHP5. Para isso, pensei em reescrever o código [deste exemplo](/index.php?p=44) de cliente PHP que tinha escrito um tempo atrás.
O primeiro passo, lógico, foi instalar o PHP5 no meu Ubuntu. Nenhum mistério até aqui, é o mesmo procedimento que venho fazendo desde o PHP3, apenas com a adição da opção --enable-soap no na execução do configure.
Depois de uma pesquisada no http://www.php.net/manual/ consegui fazer algums ensaios. Existe a opção de instanciar a classe SoapClient passando os parâmetros, desta forma:
`
$client = new SoapClient(null, array('location' => "http://localhost/soap.php",
                                     'uri'      => "http://test-uri/",
                                     'style'    => SOAP_DOCUMENT,
                                     'use'      => SOAP_LITERAL));
`
indicando o servidor e a uri onde o serviço está instalado, mas depois de várias tentativas não obtive sucesso. A construção que parece ser a oficial e mais correta é :
`
$client = new SoapClient("some.wsdl");
`
Então precisava criar o arquivo wsdl contendo a descrição e os detalhes do serviço. Como gerar algo deste gênero não é uma coisa trivial e muito menos produtiva pesquisei algo que me ajude neste sentido. Nestas pesquisas acabei encontrando este [artigo](http://www.javafree.com.br/home/modules.php?name=Content&pa=showpage&pid=42) que explica a utilização do Axis.
Usando as palavras do autor:
[...]Axis é um conjunto de ferramentas para desenvolver WebServices. Dentre suas principais funcionalidades estão:
* implementação do protocolo SOAP;
* implementação de classes para agilizar a comunicação e a publicação de Web Services;
* utiliza containers JSP para disponibilizar os Web Services na rede[...]
Ele substitui a utilização do pacote soap que usei no texto anterior. Além de gerar o wsdl e armazenar o serviço, o deploy é muito simples.Basta renomear o arquivo CalcService.java para CalcService.jws e copiá-lo para a pasta webapps/axis/ do Tomcat, colocando o arquivo .class no diretório webapps/axis/WEB-INF/jwsClasses do Tomcat.
Assim ficou fácil a criação do cliente em PHP5:
`
//criação do cliente. o arquivo wsdl é gerado automaticamente pelo Axis
$client = new SoapClient("http://localhost:8080/axis/CalcService.jws?wsdl");
echo $client->add(1,2)."";
echo $client->subtract(20,2)
`
Realmente o cliente SOAP do PHP5 ficou excelente, facilitando muito a integração da linguagem com os webservices.
Já havia ouvido falar do Axis mas nunca tinha tido a oportunidade de testá-lo. É uma ferramenta muito interessante e prática. A maneira como o wsdl é gerado facilita muito o desenvolvimento.
