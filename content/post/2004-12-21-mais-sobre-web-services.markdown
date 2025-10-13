---
categories:
- docs
- home
- python
comments: true
date: 2004-12-21T17:32:17Z
slug: mais-sobre-web-services
title: Mais sobre Web Services
url: /2004/12/21/mais-sobre-web-services/
wordpress_id: 44
---

Ultimamente tenho estudado bastante sobre Web Services. O motivo por esse interesse é meu interesse sobre Grid Computing. A versão 3 do Globus Toolkit (GT3)  é baseada no conceito de Grid Services,  ou seja, os recursos do Grid são acessíveis na forma de serviços. Para entender melhor como funciona a teoria por trás dos Web Services estou lendo um excelente livro, o Professional Java Web Services, da Wrox. Também fiz alguns testes para ver como funciona o esquema. Vou tentar aqui colocar algumas coisas que fiz. Pode ser que auxilie alguém a começar os estudos, como está me ajudando.
**O Serviço**
Para iniciar os testes eu fiz um pequeno programa em Java que será meu primeiro Web Service. O código é:
`
import java.util.*;
public class CalcService {
public int add(int p1, int p2) {`

return p1 + p2;
}

public int subtract(int p1, int p2) {

return p1 - p2;
}
}
**Publicando o serviço**
Para que o serviço possa ser acessado ele precisa ser publicado em algum servidor de aplicações. Neste caso eu escolhi o Tomcat, desenvolvido pela Apache Foundation. Fiz o download dos binários(4.1) no site http://jakarta.apache.org/ e descompactei no /usr/local/tomcat
O próximo passo é adicionar o suporte ao protocolo SOAP no Tomcat. O SOAP é o protocolo com o qual o Web Service será acessado e é padronizado pela W3C. Para fazer isso fiz o download do pacote (2.3.1) no site http://www.apache.org/dyn/closer.cgi/ws/soap/. Depois de descompactado é preciso copiar o arquivo soap-2_3_1/webapps/soap.war para o diretório de applicativos do Tomcat (cp soap-2_3_1/webapps/soap.war /usr/local/tomcat/webapps). Para testar se funcionou a instalação é só acessar o endereço http://localhost:8080/soap/
Para realizar a publicação é necessário primeiro criar um arquivo de definição no padrão WSDL. É um arquivo xml usado para definir a interface do Web Service. O arquivo que usei para esse teste foi:
`
<isd :service xmlns:isd="http://xml.apache.org/xml-soap/deployment" id="urn:onjavaserver">
<isd :provider type="java" scope="Application" methods="add subtract">
<isd :java class="CalcService"/>
</isd>
<isd :faultListener>org.apache.soap.server.DOMFaultListener</isd>
</isd>
`
Para realizar o deploy é só executar o comando:
`java -classpath soap-2_3_1/lib/soap.jar:/usr/local/tomcat/common/lib/mail.jar:/usr/local/tomcat/common/lib/activation.jar org.apache.soap.server.ServiceManagerClient http://localhost:8080/soap/servlet/rpcrouter deploy DeploymentDescriptor.xml
`
Para visualizar se o deploy funcionou pode-se usar a interface de administração do SOAP:
http://localhost:8080/soap/admin/index.html
Na opção List é possível ver os serviços.
O próximo passo é copiar o CalcService.class para o diretório de classes do soap para que o servidor de aplicações possa executar. :
`
cp CalcService.class /usr/local/tomcat/webapps/soap/WEB-INF/classes/
`
**Os clientes**
Para realizar os testes fiz tres pequenos clientes para "consumir" o serviço.
O primeiro, em Java:
`
import java.io.*;
import java.net.*;
import java.util.*;
import org.apache.soap.*;
import org.apache.soap.rpc.*;`

public class CalcClient {

public static void main(String[] args) throws Exception {

URL url = new URL ("http://localhost:8080/soap/servlet/rpcrouter");

Integer p1 = new Integer(args[1]);
Integer p2 = new Integer(args[2]);

// Constroi a chamada.
Call call = new Call();
call.setTargetObjectURI("urn:onjavaserver");
call.setMethodName(args[0]);
call.setEncodingStyleURI(Constants.NS_URI_SOAP_ENC);
Vector params = new Vector();
params.addElement(new Parameter("p1", Integer.class, p1, null));
params.addElement(new Parameter("p2", Integer.class, p2, null));
call.setParams (params);

// faz a chamada
Response resp = call.invoke(url, "" );
Parameter result = resp.getReturnValue();
System.out.println(args[0]+"="+result.getValue());
}
}

O resultado da execução:
`
elm@elm:~/documentos/soap $ java CalcClient subtract 5 1
subtract=4
elm@elm:~/documentos/soap $ java CalcClient add 5 1
add=6
`
A segunda versão do cliente, em PHP:
`
include("SOAP/Client.php");`

$soapclient = new SOAP_Client('http://localhost:8080/soap/servlet/rpcrouter');
$soapoptions = array('namespace' => 'urn:onjavaserver','trace' => 0);
//chamando a função add
$ret = $soapclient->call('add', $params = array(1,2), $soapoptions);

if (PEAR::isError($ret))
{    // tratamento de erros
}
else
{
echo "Add=".$ret;
}
//chamando a função substract
$ret = $soapclient->call('subtract', $params = array(10,2), $soapoptions);

if (PEAR::isError($ret))
{    // tratamento de erros
}
else
{
echo "Subtract=".$ret;
}
E o terceiro, em python:
`
#cliente python usando servico feito em java rodando em servidor tomcat
from SOAPpy import SOAPProxy
server = SOAPProxy('http://127.0.0.1:8080/soap/servlet/rpcrouter')
print '2 + 2 = ' + str(server._ns("urn:onjavaserver").add(2,2))
print '5 - 2 = ' + str(server._ns("urn:onjavaserver").subtract(5,2))
`
A funcionalidade dos três é a mesma, mas o tamanho do código é interessante. O código em python é muito menor e mais fácil de se entender, IMHO.
Esses foram apenas alguns testes que fiz para tentar entender o funcionamento dos Web Services.
