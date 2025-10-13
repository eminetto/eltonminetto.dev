<html>
<script>
/*funcao que cria o objeto XMLHttpRequest de acordo com o navegador do usuario */
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

function Procura(value){
   /*usa o objeto XMLHttpRequest para enviar o request. get é a forma de envio dos dados.
   funcoes_ajax2.php é o nome do script que irá processar as informacoes com os parametros.	*/	
   http.open('get', 'funcoes_ajax2.php?op=mostraNome&cd_usuario='+value); 
   http.onreadystatechange = handleResponse; //o nome da funcao javascript que irá processar o resultado
   http.send(null);
}

/*funcao que processa o resultado*/
function handleResponse() {
   if(http.readyState == 4){
       var response = http.responseText;
       document.getElementById('nome').innerHTML = response; //o resultado sera mostrado na DIV chamada nome
   }
}

</script>
<body>
<form name="teste">
	<input type="text" name="codpes" onchange="Procura(this.value)">
	<div id="nome"></div>
</form>
</body>
</html>
