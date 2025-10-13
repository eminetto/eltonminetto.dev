<?
include("include/cpaint/cpaint2.inc.php"); //include da biblioteca do cpaint. obrigatório

function busca_nome() { //função que sera executada
	$con = mysql_connect("localhost","eminetto","elm2006net");
	$codpes =$_POST[cpaint_argument][0]; //busca do argumento recebido. são enviados no array cpaint_argument
	mysql_select_db("eminetto");
	$res = mysql_query("select nompes from pessoa where codpes=$codpes",$con);
	$row = mysql_fetch_object($res);
	$nompes = $row->nompes;
	mysql_close($con);
	echo  $nompes; //imprime o valor de retorno. ele será recebido pelo outro script
}

switch($_POST[cpaint_function]) { //testa o nome da funcao sendo chamada e executa
	case "busca_nome";
		busca_nome();
		break;
}
?>
