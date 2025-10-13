<?
/* comentarios e formulario para novo comentario*/
function comentarios($id_post) {
	if(!$id_post) //se não existe valor na variavel usa a enviada por GET
		$id_post = $_GET[id_post];
	$post = new tabela("post");//conexao com a tabela post
	$post->get(array("*"),"id_post=$id_post"); //busca os dados do post solicitado
	$post->result();
	$dados[tit_post] = $post->tit_post; //alimenta o vetor de dados para a visão
	$dados[ds_post] = $post->ds_post;
	$dados[dt_post] = $post->dt_post;
	$dados[id_post] = $post->id_post;
	$comentario = new tabela("comentario"); //conexao com a tabela comentario
	$comentario->get(array("*"),"id_post=$id_post");
	$i=0;
	while($comentario->result()) { //mostra todos os comentarios do post
		$dados[ds_com][$i] = $comentario->ds_com;
		$dados[email_com][$i] = $comentario->email_com;
		$i++;
	}
	app::showView("view/comentario_view.php",$dados);
}
?>
