<?
/* faz as validacoes de login e mostra os posts cadastrados*/
function login() {
	session_start();
	if(!$_SESSION[logado]) {
		//fazer as validações aqui
		$_SESSION[username] = $_POST[username];
		$_SESSION[logado] = 1;
	}

	$post = new tabela("post"); 
	$post->get(array("*"));//busca todos os posts da tabela para mostrar na administração
	$i=0;
	while($post->result()) {
		$dados[tit_post][$i] = $post->tit_post;
		$dados[id_post][$i] = $post->id_post;
		$i++;
	}
	app::showView("view/admin_view.php",$dados);
}
?>
