<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>Título</title></head>
<link href="view/estilo.css" rel="stylesheet" type="text/css" />
<div id="head">Minhas finanças</div>
<div id="login">
<form name="login" method="post">
<input type="hidden" name="op" value="cadastrar">
<table>
<tr>
	<td>E-mail:</td>
	<td><input type="text" name="email_usu" value="<?=$valor[email]?>">&nbsp;<?=$erro[email]?></td>
</tr>
<tr>
	<td>Nome completo:</td>
	<td><input type="text" name="nome_usu" value="<?=$valor[nome]?>"><?=$erro[nome]?></td>
</tr>
<tr>
	<td>Senha:</td>
	<td><input type="password" name="senha_usu"><?=$erro[senha]?></td>
</tr>
<tr>
	<td>Confirmar a senha:</td>
	<td><input type="password" name="senha_2"></td>
</tr>
<tr>
	<td><input type="submit" value="Cadastrar"></td>
	<td><a href="index.php">Voltar</td>
</tr>
</table>
</form>
