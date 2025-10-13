<?php
/*funcao que recebe um array e retorna uma string formatada
 * para que a funcao window.eval do javascript consiga entender e
 * montar a matriz
 * formato da string: [["campo","valor"], ["campo2","valor2"]]
 */ 
function retornoJS($arr) {
	$txt = '[';
	foreach ($arr as $fieldname => $fieldvalue) {
		$txt .= '["'.$fieldname.'","'.$fieldvalue.'"],';
	}
	$txt = substr($txt,0,strlen($txt)-1);//remove a ultima virgula
	$txt .= ']';
	return $txt;
}

$db = mysql_connect('localhost', 'eminetto', 'elm2006net');
mysql_select_db('eminetto');
$query = "SELECT nome,cpf,rg FROM cliente where codigo=$_GET[codigo]";
//query
$dbresult = mysql_query($query, $db);
while($row = mysql_fetch_assoc($dbresult)) {
	foreach ($row as $fieldname => $fieldvalue) {
		$arr[$fieldname] = $fieldvalue; //monta um array no formato $arr[nome_do_campo] = valor
	}
}
mysql_close();
echo retornoJS($arr);
?>
