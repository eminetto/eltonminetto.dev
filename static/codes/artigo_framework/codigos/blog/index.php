<?
session_start();
include("../classes/app.php");
include("../classes/tabela.php");

class blog extends app {

	/* Mostra a pagina inicial*/
	function index(){
		$post = new tabela("post");
		$com = new tabela("comentario");
		$post->get(array("*"));
		$i=0;
		while($post->result()) {
			$dados[tit_post][$i] = $post->tit_post;
			$dados[dt_post][$i] = $post->dt_post;
			$dados[ds_post][$i] = nl2br($post->ds_post);
			$dados[id_post][$i] = $post->id_post;
			$com->get(array("count(*) com"),"id_post=$post->id_post");
			$com->result();
			$dados[num_com][$i] = $com->com;
			$i++;
		}
		unset($post);
		app::showView("view/index_view.php",$dados); //chama o template
	}

	/* comentarios e formulario para novo comentario*/
	function comentarios($id_post) {
		if(!$id_post)
			$id_post = $_GET[id_post];
		$post = new tabela("post");
		$post->get(array("*"),"id_post=$id_post");
		$post->result();
		$dados[tit_post] = $post->tit_post;
		$dados[ds_post] = $post->ds_post;
		$dados[dt_post] = $post->dt_post;
		$dados[id_post] = $post->id_post;
		$comentario = new tabela("comentario");
		$comentario->get(array("*"),"id_post=$id_post");
		$i=0;
		while($comentario->result()) {
			$dados[ds_com][$i] = $comentario->ds_com;
			$dados[email_com][$i] = $comentario->email_com;
			$i++;
		}
		app::showView("view/comentario_view.php",$dados);
	}

	/* faz a inclusao do comentario na base*/
	function addComentario(){
		$com = new tabela("comentario");
		$com->id_post = $_POST[id_post];
		$com->ds_com = $_POST[ds_com];
		$com->email_com = $_POST[email_com];
		$com->insert();
		$com->save();
		blog::comentarios($_POST[id_post]);
	}

	/* mostra o formulario de login*/
	function mostraLogin() {
		session_start();
		if(!$_SESSION[logado]) {
			app::showView("view/login_view.php");
		}
		else {
			blog::login();
		}
	}

	/* faz as validacoes de login e mostra os posts cadastrados*/
	function login() {
		session_start();
		if(!$_SESSION[logado]) {
			//fazer as validaÃ§Ãµes
			$_SESSION[username] = $_POST[username];
			$_SESSION[logado] = 1;
		}
	
		$post = new tabela("post");
		$post->get(array("*"));
		$i=0;
		while($post->result()) {
			$dados[tit_post][$i] = $post->tit_post;
			$dados[id_post][$i] = $post->id_post;
			$i++;
		}
		app::showView("view/admin_view.php",$dados);
	}

	/* faz a inclusao do post*/
	function addPost() {
		$post = new tabela("post");
		$post->tit_post = $_POST[tit_post];
		$post->ds_post = $_POST[ds_post];
		$post->dt_post = date('Y-m-d H:i:s');
		$post->insert();
		$post->save();
		blog::login();
	}

	/* faz a exclusao do post*/
	function del() {
		$post = new tabela("post");
		$post->delete("id_post=$_GET[id_post]");
		$post->save();
		blog::login();
	}

	/* faz a alteracao do post*/
	function altPost() {
		$post = new tabela("post");
		$post->tit_post = $_POST[tit_post];
		$post->ds_post = $_POST[ds_post];
		$post->update("id_post=$_POST[id_post]");
		$post->save();
		blog::login();
	}

	function buscaPost() {
		include("../classes/JSON.php");
		$post = new tabela("post");
		$post->get(array("*"),"id_post=$_GET[id_post]");
		$post->result();
		$arr[id_post] = $post->id_post;
		$arr[ds_post] = $post->ds_post;
		$arr[tit_post] = $post->tit_post;

		$json = new Services_JSON();
		echo $json->encode($arr);
		}
}

$blog = new blog("mysql://root:@localhost/blog");
?>
