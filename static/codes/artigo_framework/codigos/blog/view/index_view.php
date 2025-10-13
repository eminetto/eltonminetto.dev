<html>
<head>
<title>Blog</title>
<link href="view/estilo.css" rel="stylesheet" type="text/css"></head>
<body>
<h2>Blog</h2> 

<a href="index.php?op=mostraLogin">Admin</a>
<br><br>
<?
for($i=0;$i<count($tit_post);$i++){
?>
<div class="titulo"><?=$tit_post[$i]?></div>
<div class="data">Postado em <?=$dt_post[$i]?></div><br>
<div class="corpo"><?=$ds_post[$i]?></div><br>
<div class="comentario"><?=$num_com[$i]?> comentario(s)  <a href="index.php?op=comentarios&id_post=<?=$id_post[$i]?>">Adicionar comentário</a></div><br>
<?
}
?>
</body>
</html>
