<?
/* faz a inclusao do comentario na base*/
function addComentario(){
	$com = new tabela("comentario"); //conecta com a tabela comentario
	$com->id_post = $_POST[id_post]; //altera o valor de acordo com os enviados pelo form 
	$com->ds_com = $_POST[ds_com];
	$com->email_com = $_POST[email_com];
	$com->insert(); //insere
	$com->save(); //faz o commit
	blog::comentarios($_POST[id_post]); //redireciona para o metodo comentarios
}
?>
