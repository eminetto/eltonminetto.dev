<html>
<head>
<title>Blog</title>
<link href="view/estilo.css" rel="stylesheet" type="text/css"></head>
<body>
<h2>Blog</h2> 

<div class="titulo"><?=$tit_post?></div><br/>
<div class="data">Postado em <?=$dt_post?></div><br />
<div class="corpo"><?=$ds_post?></div><br />

<h3>Comentários</h3>
<?
for($i=0;$i<count($ds_com);$i++) {
?>
<?=$ds_com[$i]?>	
<div class="data">Comentado por <?=$email_com[$i]?></div><br />
<?

} 
?>

<h3>Adicionar Comentário</h3>

<form method="post" action="index.php" name="comentario">
	<input type="hidden" value="addComentario" name="op">
	<input type="hidden" value="<?=$id_post?>" name="id_post">
	<label>E-Mail</label>
	<input type="text" name="email_com"><br>
	<label>Comentario</label>
	<textarea rows="10" cols="40" name="ds_com"></textarea>
	<br>
	<input type="submit" value="Enviar"><br>
</form>
<br>
<br>
<a href="index.php">Voltar</a><br>
</body>
</html>
