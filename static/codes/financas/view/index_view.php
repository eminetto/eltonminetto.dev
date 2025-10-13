<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>Minhas Finanças (beta)</title></head>
<link href="view/estilo.css" rel="stylesheet" type="text/css" />
<div id="head">Minhas finanças (beta)</div>

<div id="login">
<?
if(!$_SESSION[cod_usu]) {
	?>
	<form name="login" method="post">
	<input type="hidden" name="op" value="login">
	<table>
		<tr>
			<td>E-mail:</td>
			<td><input type="text" name="email_usu"></td>
		</tr>
		<tr>
			<td>Senha: </td>
			<td><input type="password" name="senha_usu"></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Entrar"></td>
		</tr>
	</table>
	</form>
	<a href="index.php?op=cadastrar">Cadastrar</a>
	<?
}
else {
	?>
	<script>
		window.open('index.php?op=login','_self');
	</script>
	<?
}	
?>
</div>
<div id="info">
<h2>O que é?</h2>
<p>Uma aplicação Web para controle de finanças pessoais.</p>
<h2>O que ela faz?</h2>
<p>Como é uma aplicação em estágios iniciais de desenvolvimento ela possui poucos recursos ainda. É possível controlar os rendimentos e despesas mês a mês. Assim que o desenvolvimento avançar serão adicionadas novas características como controle de cheques, avisos por e-mail de despesas em vencimento etc.</p>
<h2>O que é preciso para usar?</h2>
<p>Por enquanto funciona apenas no Firefox mas deverá funcionar em qualquer navegador moderno </p>
<h2>É de graça?</h2>
<p>Sim</p>
<h2>Eu não gostei, achei feio, para quem eu reclamo? E se eu tiver alguma sugestão?</h2>
<p>Você pode comentar <a href="http://www.eltonminetto.net/minha-aplicacao-web-20.htm" target="novo">aqui</a></p>
</div>
<div id="foot"><a href="http://www.eltonminetto.net">Elton Minetto</a></div>
