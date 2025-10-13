<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>Minhas finanças (beta)</title></head>
<link href="view/estilo.css" rel="stylesheet" type="text/css" />
<script src="js/ajax.js"></script>
<script>
	var anoAtual;
	
	/* função que mostra os anos cadastrados*/
	function mostraAnos() {
		send('index.php?op=mostraAnos', retornoMostraAnos);
	}
	
	function retornoMostraAnos() {
		if(http.readyState == 4){
			var response = http.responseText;
			document.getElementById('anos').innerHTML = response;
			fim();
		}
	}	    
		
	/* funcao que busca os dados do ano*/
	function buscaAno(ano) {
		anoAtual = ano;//atualiza o ano atual
		document.getElementById('ano'+anoAtual).className = 'negativo';
		send('index.php?op=mostraAno&ano='+anoAtual, retornoBuscaAno);
	}
	
	/* função de retorno do ano*/
	function retornoBuscaAno() {
   	 if(http.readyState == 4){
			var response = http.responseText;
			document.getElementById('dados').innerHTML = response;
			atualizaSaldo();
        	fim();
    	}	    
	}
	/*funcao que atualiza os campos das despesas*/	
	function atualizaDesp(campo) {
		send('index.php?op=atualizaDesp&name='+campo.name+'&value='+campo.value,retornoAtualizaDesp);
	}
	/*retorno da atualizacao*/
	function retornoAtualizaDesp() {
		if(http.readyState == 4){
			atualizaSaldo();
		}
	}
	
	/* funcao que faz a insercao de uma despesa*/
	function insereDespesa() {
		desc_desp = document.novoDesp.desc_desp.value;
		categ_desp = document.novoDesp.categ_desp.value;
		send('index.php?op=insereDespesa&desc_desp='+desc_desp+'&categ_desp='+categ_desp,retornoInsereDespesa);
	}
	
	function retornoInsereDespesa() {
		if(http.readyState == 4){
			buscaAno(anoAtual);
		}
	}

	/*funcao que atualiza os campos dos rendimentos*/	
	function atualizaRend(campo) {
		send('index.php?op=atualizaRend&name='+campo.name+'&value='+campo.value,retornoAtualizaRend);
	}
	/*retorno da atualizacao*/
	function retornoAtualizaRend() {
		if(http.readyState == 4){
			atualizaSaldo();
		}
	}

	/* funcao que faz a insercao de um rendimento*/
	function insereRendimento() {
		desc_rend = document.novoRend.desc_rend.value;
		categ_rend = document.novoRend.categ_rend.value;
		send('index.php?op=insereRendimento&desc_rend='+desc_rend+'&categ_rend='+categ_rend,retornoInsereRendimento);
	}
	
	function retornoInsereRendimento() {
		if(http.readyState == 4){
			buscaAno(anoAtual);
		}
	}
	
	/* funcao que adiciona o ano*/
	function insereAno(campo) {
		send('index.php?op=insereAno&ano='+campo.value,retornoInsereAno);
	}
	
	function retornoInsereAno() {
		if(http.readyState == 4){
			mostraAnos();
		}
	}

	/* funcao que atualiza o saldo*/
	function atualizaSaldo() {
		send('index.php?op=atualizaSaldo&ano=2006', retornoAtualizaSaldo);
	}
	
	/* função de retorno do ano*/
	function retornoAtualizaSaldo() {
		if(http.readyState == 4){
			var response = http.responseText;
			document.getElementById('saldo').innerHTML = response;
			fim();
		}	    
	}
	
	/* funcao que faz a exclusao de uma renda*/
	function delRendimento(cod_rend) {
		send('index.php?op=delRendimento&cod_rend='+cod_rend+'&ano='+anoAtual,retornoDelRendimento);
	}
	
	function retornoDelRendimento() {
		if(http.readyState == 4){
			buscaAno(anoAtual);
		}
	}

	/* funcao que faz a exclusao de uma despesa*/
	function delDespesa(cod_desp) {
		send('index.php?op=delDespesa&cod_desp='+cod_desp+'&ano='+anoAtual,retornoDelDespesa);
	}
	
	function retornoDelDespesa() {
		if(http.readyState == 4){
			buscaAno(anoAtual);
		}
	}
	
	function mostraFormAddRend() {
		document.getElementById('formAddRend').style.visibility = "visible";
		document.getElementById('formAddRend').style.position = "relative";
	}
	
	function escondeFormAddRend(){
		document.getElementById('formAddRend').style.visibility = "hidden";
		document.getElementById('formAddRend').style.position = "absolute";
	}

	function mostraFormAddDesp() {
		document.getElementById('formAddDesp').style.visibility = "visible";
		document.getElementById('formAddDesp').style.position = "relative";
	}
	
	function escondeFormAddDesp(){
		document.getElementById('formAddDesp').style.visibility = "hidden";
		document.getElementById('formAddDesp').style.position = "absolute";
	}
	
	function categorias(categ) {
		tmp = document.getElementById(categ).className;
		if(tmp == 'oculto')
			document.getElementById(categ).className='normal';
		else
			document.getElementById(categ).className='oculto';
	}
	
	function atualizaAD() {
		send('index.php?op=adsense',retornoAtualizaAD);
	}
	
</script>
<body onload="mostraAnos();">
<div id="head">Minhas finanças (beta)</div>

Bem-vindo <?=$_SESSION[nome_usu]?><br>
<a href="index.php?op=logout">Sair</a>


<div id="anos"></div>
<div id="itens">
	<div id="dados"></div>
	<div id="saldo"></div>
</div>
<div id="foot"></div>
<br><br>
<div id="google">
<script type="text/javascript">
			google_ad_client = "pub-2610146309768303";
			google_ad_width = 728;
			google_ad_height = 90;
			google_ad_format = "728x90_as";
			google_ad_type = "text";
			google_ad_channel ="";
		</script>
		<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
</div>

</body>
</html>
