<?
//faz a inclusão da biblioteca Sajax
require("include/Sajax.php");

// Baseado nos exemplos desenvolvidos por Leonardo Lorieri

/* funcao PHP que recebe o código e faz a pesquisa no banco de dados retornando o nome*/
function mostra_nome($codpes) { 
	$con = mysql_connect("localhost","eminetto","elm2006net");
	mysql_select_db("eminetto");
        $res = mysql_query("select nompes from pessoa where codpes=$codpes",$con);
	$row = mysql_fetch_object($res);
	$nompes = $row->nompes;
        mysql_close($con);
	return $nompes;
}

$sajax_request_type = "GET"; //forma como os dados serao enviados
sajax_init(); //inicia o SAJAX 
sajax_export("mostra_nome"); // lista de funcoes a ser exportadas
sajax_handle_client_request();// serve instancias de clientes

?>
<html>
<head>
       <title>Nome </title>
       <script>
       <?
       sajax_show_javascript(); //gera o javascript
       ?>

       function mostra(nome) { //esta funcao retorna o valor para o campo do formulario
               document.teste.nompes.value=nome;
       }

       function get_nome(c) { //esta funcao chama a funcao PHP exportada pelo Ajax
                        cod = c.value;
                        x_mostra_nome(cod, mostra);//chama a funcao x_mostra_nome que será gerada pelo sajax. o primeiro parametro é o codigo e o segundo é a funcao JavaScript que tratara o retorno, no caso a mostra

       }
       </script>

</head>
<body>
<form name="teste">
        <input type="text" name="codpes" onchange="get_nome(this)">
        <input type="text" name="nompes">
</form>
</body>
</html>
