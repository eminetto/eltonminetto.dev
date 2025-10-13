+++
bigimg = ""
title = "Usando AWS API Gateway e AWS Lambda"
subtitle = ""
date = "2017-01-04T08:58:10-02:00"

+++

Recentemente lançamos uma nova versão do site da Coderockr (já viu? Está [lindão!](http://coderockr.com)) e nosso [desenvolvedor/designer](https://www.facebook.com/raonymarcondes) apresentou uma ótima ideia: criarmos um [formulário de contato](http://coderockr.com/contact.html) mais inteligente, onde o interessado pode nos fornecer informações mais completas já no primeiro momento, acelerando nosso processo de negociação. 

Como usamos o [CRM da HubSpot](https://www.hubspot.com/products/crm) para gerenciar nossos contatos com clientes, rapidamente a ideia evoluiu para usarmos a API e integrarmos o formulário com o CRM. 

<!--more-->

Mas nesse momento enfrentamos um problema, pois algumas limitações da API não permitiram a integração usando JavaScript direto do navegador (a API do HubSpot não permite CORS...). A solução seria termos um script, em PHP por exemplo, que receberia os dados do formulário e os enviaria para a API, usando CURL. Como o site da Coderockr foi desenvolvido usando o Github Pages precisaríamos de um servidor para executar esse script, o que aumentaria o custo de execução e manutenção do site. Neste momento lembramos da solução API Gateway + Lambda, da Amazon Web Services. 

O primeiro passo foi a criação da "função lambda", que conteria a lógica necessária para realizar a operação de envio dos dados para o HubSpot. Usando o painel da AWS criamos uma função chamada "contato" e optamos por desenvolvê-la usando Python (as outras opções eram C#, Java e NodeJS). O código, com pequenas alterações, pode ser visto neste [Gist](https://gist.github.com/eminetto/dfeade6e3bca92496ba5ecbb89d51580). Para mais detalhes sobre a API do HubSpot é possível acessar a [documentação oficial](https://developers.hubspot.com). 

[![lamda](/images/posts/postAWS/lambda.png)](/images/posts/postAWS/lambda.png) 

Com o código criado o próximo passo foi a criação de uma API, que recebe as informações do formulário e as repassa para a função lambda. Para isso usamos o serviço API Gateway e criamos o recurso chamado /contato. Um detalhe importante é que para podermos acessar essa API facilmente pelo navegador é preciso habilitarmos a opção "Enable CORS", no menu Actions. 

[![api](/images/posts/postAWS/api.png)](/images/posts/postAWS/api.png)

Também é preciso criarmos um "Stage", na opção correspondente no menu na esquerda. Neste exemplo criamos apenas um, chamado *prod* e com isso temos uma url para acesso e opções de configuração avançada de segurança, cache e logs. 

[![stages](/images/posts/postAWS/stages.png)](/images/posts/postAWS/stages.png)

O passo final é configurar a função lambda para ser executada sempre que receber uma mensagem da API. No painel de configuração da função podemos usar a opção "Triggers" para fazer a conexão entre a API e nossa lógica. 

[![trigger](/images/posts/postAWS/trigger.png)](/images/posts/postAWS/trigger.png)

Podemos fazer um teste de conexão usando o painel de configuração da API para garantirmos que tudo está funcionando

[![api_lambda](/images/posts/postAWS/api_lambda.png)](/images/posts/postAWS/api_lambda.png)

Assim bastou escrevermos um pouco de JavaScript para que o formulário fizesse um post para a nova API fornecida pelo Gateway e ela executa a função lambda e envia os dados para o HubSpot. E nós só pagamos para a Amazon apenas os segundos gastos nesse processo, sem a necessidade de mantermos um servidor rodando PHP ou outra linguagem. 

O resultado final é uma nova entrada no nosso CRM e um e-mail na  caixa postal do responsável por entrar em contato com os clientes, enviado automaticamente pelo próprio HubSpot.

[![hubspot](/images/posts/postAWS/hubspot.png)](/images/posts/postAWS/hubspot.png)

Como todo o site é formado por páginas estáticas, fornecidas pelos servidores do Github, podemos dizer que temos uma solução "serverless" e barata. E mais um motivo para aprender algo novo ;)
