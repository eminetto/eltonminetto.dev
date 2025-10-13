<?php
/* mais informacoes sobre JSON no site www.json.org */
include("JSON.php"); //download em http://pear.php.net/pepr/pepr-proposal-show.php?id=198
$db = mysql_connect('localhost', 'root', '');
mysql_select_db('curso');
$query = "SELECT codigo,nome,cpf,rg FROM pessoa where codigo=$_GET[codigo]";
//query

$dbresult = mysql_query($query, $db);
$retorno = "var dados = new Array();\n\n";

$row = mysql_fetch_assoc($dbresult);
mysql_close();
$json = new Services_JSON();
echo $json->encode($row);
?>
