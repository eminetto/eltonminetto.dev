<html>
<body>
<?
if(!$_POST[nome]) { //mostra o formulario
	?>
	<script language="JavaScript">
		var filhos=0;	
		function addFilho() {
			//pega a div 
			var DivFilhos = document.getElementById('filhos');
			//cria uma nova div
			var DivNovoFilho = document.createElement('div');
			//altera o nome da div
			DivNovoFilho.setAttribute("id","DivFilho"+filhos);
			//coloca no innerHTML da nova div o codigo html necessario
			DivNovoFilho.innerHTML = 'Nome do filho:<input type="text" name="filho['+filhos+']"> <input type="button" value="X" onClick="delFilho(\'DivFilho'+filhos+'\')"><br>';
			//adiciona a nova div como filha da div filhos
			DivFilhos.appendChild(DivNovoFilho);
			//incrementa o numero de filhos
			filhos++;
		}
		//função que exclui um filho
		function delFilho(divNum){
			var d = document.getElementById('filhos');
			var olddiv = document.getElementById(divNum);
			//remove a div 
			d.removeChild(olddiv);
		}
	</script>
	<form name="teste" action="form_dinamico.php" method="post">
	Nome:<input type="text" name="nome"><br>
	Filhos<br>
	<div id="filhos"></div>
	<input type="button" value="Adicionar filho" onclick="addFilho()">
	<br>
	<input type="submit" value="Enviar">
	</form>
<?
}
else {//mostra os dados
	echo $_POST[nome]."<br>";
	$filhos = $_POST[filho];
	if (!empty($filhos)){	
		foreach ($filhos as $filho){
			echo $filho."<br>";
		}
	}
}
?>	
</body>
</html>
