<html>
<body>
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

function Procura(codigo){
	http.open('get', 'multiplo_ajax.php?codigo='+codigo.value); 
	http.onreadystatechange = handleResponse;
	http.send(null);
}
var arr; //array com os dados retornados
function handleResponse() {
	if(http.readyState == 4){
		var response = http.responseText; //resultado formatado
		arr=window.eval(response);  //a funcao eval transforma em uma matriz
		for (var p = 0; p < arr.length; p++ ) {
			//posicao arr[p][0] eh o nome do campo
			//posicao arr[p][1] eh o valor do campo
            document.getElementById(arr[p][0]).value = arr[p][1]; //localiza o campo do formulario pelo seu ID e coloca o valor
		}
		
	}
}
</script>

<form name="test" action="lixo.php">
	Codigo:<input type="text" name="codigo" onchange="Procura(this)"><br>
	Nome:<input type="text" name="nome" id="nome"><br> 
	CPF:<input type="text" name="cpf" id="cpf"><br>
	RG:<input type="rg" name="rg" id="rg">
</form>
</body>
</html>
