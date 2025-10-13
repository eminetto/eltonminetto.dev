+++
bigimg = ""
date = "2017-04-12T15:29:24-03:00"
subtitle = ""
title = "Usando a API da TotalVoice para enviar SMS em PHP"

+++

Imagine a situação. Você está no conforto da sua casa (ou no conforto do seu pub favorito) e algo importante acontece em seu site/produto/projeto. Algo como um erro no banco de dados ou um cliente que acaba de fazer uma compra de vários dígitos.

Em ambos os exemplos seria bem útil você receber algum tipo de aviso sobre o ocorrido, seja para resolver o problema ou para pagar uma nova rodada de cerveja no segundo caso.

<!--more-->

Uma forma simples e rápida de realizar isso é enviando um SMS para o responsável no momento que o evento ocorreu. Uma das formas mais simples de realizar isso é usar uma API, como a fornecida pelos meus amigos da TotalVoice.

A [TotalVoice](http://www.totalvoice.com.br/) é uma startup de Santa Catarina e que está recebendo [destaque no mercado](http://www.startupsc.com.br/startup-catarinense-totalvoice-recebe-investimento-da-bossa-nova/). A API deles é bem fácil de entender e eles tem um exemplo de uso em PHP no [Github](https://github.com/totalvoice/TotalVoiceAPI-PHP/blob/master/TotalVoiceAPI.class.php). Mas, e fica aqui a sugestão para a equipe da TotalVoice, o exemplo oficial é bem “old school”, então fiz uma pesquisa no Packagist e encontrei um repositório [extra-oficial](https://github.com/minerva-framework/totalvoice-api) que tem uma versão mais “moderna”.

O primeiro passo é criar uma conta no site, colocar alguns reais em créditos e pegar o seu token de acesso para a API.

Para começar a testar sem tirar o escorpião do bolso você pode enviar um e-mail para “[sucesso@totalvoice.com.br](mailto:sucesso@totalvoice.com.br)”, informando sua conta e solicitando um crédito bônus de R$ 5,00.

Com essa informação basta instalar o pacote usando:

	php composer.phar require minerva-framework/totalvoice-api

E criar um código similar a este:

```php
<?php

require 'vendor/autoload.php';

use Minerva\TotalVoice\TotalVoice;
use Minerva\TotalVoice\SMS\SMS;


$sms = new SMS();
$sms->setNumber(47999996666);
$sms->setText("Venda bilionária feita! Pagar mais cervejas!!");

TotalVoice::$token = 'SEU_TOKEN';
$response = TotalVoice::sendSms($sms);
var_dump($response->getContent());

```

Lendo a [documentação da API](https://api.totalvoice.com.br/doc/) é possível ver que o envio de SMS é só uma das [funcionalidades disponíveis](http://www.totalvoice.com.br/api/aplicacoes/). Fiquei particularmente interessado no recurso de Webhooks para poder controlar o status das ligações telefônicas e SMSs de forma automática. Dá para criar soluções interessantes com isso.

Bom, fica aqui a dica de uma solução nacional e fácil de usar que pode ser bem útil em vários cenários além dos que eu comentei aqui.