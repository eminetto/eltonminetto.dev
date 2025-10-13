<?
if($_POST[contact_name]) {
	$contact_name = strtoupper($_POST[contact_name]);
	$con = mysql_connect("localhost","eminetto","elm2006net");
	mysql_select_db("eminetto");
	//faz a consulta no banco procurando se existe no nome as letras digitadas
	$res = mysql_query("select distinct nompes,codpes from pessoa where upper(nompes) like '%$contact_name%' order by nompes",$con);
	//é preciso retornar uma lista em html
	$texto = '<ul>';
	while($row = mysql_fetch_object($res)) {
		//cada ítem deve estar entre <li> e </li>
		$texto .= '<li>'.$row->nompes.' - '.$row->codpes.'</li>';
	}
	$texto .= '</ul>';
	mysql_close($con);
}
//imprime o conteudo
echo $texto;
?>
