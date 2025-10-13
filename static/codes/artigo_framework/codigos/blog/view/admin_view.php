<html>
<head><title>Blog Admin</title></head>
<link href="view/estilo.css" rel="stylesheet" type="text/css" />
<script>
       function createRequestObject() {
         var ro;
         var browser = navigator.appName;
         if(browser == "Microsoft Internet Explorer"){
            ro = new ActiveXObject("Microsoft.XMLHTTP");
         }else{
            ro = new XMLHttpRequest();
         }
         return ro;
       }
       var http = createRequestObject();
       
       function busca(id_post){
               http.open('get', 'index.php?op=buscaPost&id_post='+id_post);
               http.onreadystatechange = handleResponse;
               http.send(null);
       }
       
       function handleResponse() {
               if(http.readyState == 4){
					mostraForm();
					var response = http.responseText; //resultado formatado
					eval("var arr = "+response); //eval avalia uma expressao e executa. vai criar um objeto chamado arr com o resultado
					document.getElementById('tit_post').value = arr.tit_post; //cada indice do vetor do php vira uma propriedade do objeto
					document.getElementById('ds_post').value = arr.ds_post;
					document.getElementById('id_post').value = arr.id_post;
					document.getElementById('op').value = "altPost";
					document.getElementById('titulo').innerHTML = "Alterar Post";
				}
		}
		
		function mostraForm() {
			document.getElementById('form').style.visibility = "visible";
			document.getElementById('form').style.position = "relative";
			document.getElementById('op').value = "addPost";
			document.getElementById('titulo').innerHTML = "Adicionar Post";
		}	
		
</script>


<body>
<h2>Posts</h2>
<table>
<?
for($i=0;$i<count($tit_post);$i++) {
?>
	<tr>
		<td><?=$tit_post[$i]?></td>
		<td><a href="index.php?op=del&id_post=<?=$id_post[$i]?>">Excluir</a></td>
		<td><a href="javascript:busca(<?=$id_post[$i]?>)">Alterar</a></td>
	</tr>	
<?
}
?>
</table>
<a href="javascript:mostraForm()">Incluir</a><br>
<div id="form">
<h2><div id="titulo">Adicionar Post</div></h2>
<form name="post" action="index.php" method="post">
	<input type="hidden" name="op" value="addPost" id="op">
	<input type="hidden" name="id_post" value="" id="id_post">
	Título:<input type="text" name="tit_post" id="tit_post"><br>
	Texto:<textarea name="ds_post" cols="40" rows="10" id="ds_post"></textarea><br>
	<input type="submit" value="Enviar">
</form>
</div>
<a href="index.php">Voltar</a>
</body>
</html>
