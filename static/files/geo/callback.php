<?php
/**
 * callback.php
 *
 * Página chamada quando o usuário autoriza a aplicação no Apontador.
 *
 * O processamento dos valores retornados é feito por apontadorProcessaAutorizacao(), que gera
 * um token+secret. No exemplo, vamos guardar em um cookie e mandar para a página principal, mas
 * uma aplicação mais robusta associaria esses dados ao cadastro do usuário.
 * 
 * Copyright 2010 Apontador/LBSLocal
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http: *www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once("lib/ApontadorApi.php");
require_once("config.php");

$apontadorApi = new ApontadorApi($key, $secret, $callbackurl);

// Se tudo der certo, temos um token pra acessar a API em nome do usuário
$access_token = $apontadorApi->apontadorProcessaAutorizacao();
if (!$access_token) {
	die("Acesso inválido");
}

// Guardamos o token em um cookie e mandamos ele para a página principal
setcookie('oauth_token', $access_token['oauth_token'], time()+2592000 , '/' ) or die('seu navegador não aceita cookies');
setcookie('oauth_token_secret', $access_token['oauth_token_secret'], time()+2592000, '/');
setcookie('user_id', $access_token['user_id'], time()+2592000, '/');
header("Location:index.php?cmd=");
?>