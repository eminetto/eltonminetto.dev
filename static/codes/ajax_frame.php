<html>
<script src="ajax.js"></script>
<script>
/* função de retorno*/
function mostraRetorno() {
	if(http.readyState == 4){
		var response = http.responseText;
		document.getElementById('titulo').innerHTML = response;
		fim();
	}	
}
</script>
<div id="titulo"></div>
<a href="javascript:send('teste_frame.php?op=1',mostraRetorno)">Link 1</a><br>
<a href="javascript:send('teste_frame.php?op=2',mostraRetorno)">Link 2</a><br>
<a href="javascript:send('teste_frame.php?op=3',mostraRetorno)">Link 3</a><br>
<a href="javascript:send('teste_frame.php?op=4',mostraRetorno)">Link 4</a><br>