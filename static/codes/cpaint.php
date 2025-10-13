<html>
<head>
<title>Nome </title>
</head>
<script src="include/cpaint/cpaint.inc.js" type="text/javascript"></script>
<script type="text/javascript">

//funcao q faz a conexao para buscar o nome da pessoa
function get_nome() {
  cpaint_call(
  'funcoes_ajax.php', //nome do arquivo php que será chamado
  'POST', //método que será usado
  'busca_nome', //nome da função que será executada no arquivo funcoes_ajax.php
  teste.codpes.value, //valor do parametro q será enviado
  atualiza, //funcao javascript que receberá o valor de retorno e atualizará o campo do formulario
  'TEXT'); //modo que será recebido e enviado os dados 
}
//funcao que recebe o retorno com o nome e coloca no campo nompes
function atualiza(valorDeRetorno) {
	teste.nompes.value = valorDeRetorno;
}
</script>
<body>
<form name="teste">
<input type="text" name="codpes" onchange="get_nome()">
<input type="text" name="nompes">
</form>
</body>
</html>