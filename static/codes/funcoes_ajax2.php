<?
function mostraNome($codpes) { //função que sera executada
        $con = mysql_connect("localhost","eminetto","elm2006net");
        mysql_select_db("eminetto");
        $res = mysql_query("select nompes from pessoa where codpes=$codpes",$con);
        $row = mysql_fetch_object($res);
        $nompes = $row->nompes;
        mysql_close($con);
	if($nompes) 
	        echo  $nompes; //imprime o valor de retorno. ele será recebido pelo outro script
	else
		echo "Nao encontrado";
}

switch ($_REQUEST[op]) {
	case "mostraNome":
		mostraNome($_GET[cd_usuario]);
		break;
}
?>
