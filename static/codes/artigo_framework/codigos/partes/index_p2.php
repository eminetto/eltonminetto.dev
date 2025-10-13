<?
session_start();
include("../classes/app.php"); //faz a inclusão das classes
include("../classes/tabela.php");
 
class blog extends app { //cria uma subclasse da classe blog
	* Mostra a pagina inicial*/
	function index(){
		$post = new tabela("post"); //cria uma nova instância da classe tabela
		$com = new tabela("comentario"); //conexão com a tabela comentario
		$post->get(array("*")); //busca todos (*) os campos da tabela post
		$i=0;
		while($post->result()) { //enquanto possui resultados
			//alimenta o vetor dados que será enviado para a visão
			$dados[tit_post][$i] = $post->tit_post; 
			$dados[dt_post][$i] = $post->dt_post;
			$dados[ds_post][$i] = nl2br($post->ds_post);
			$dados[id_post][$i] = $post->id_post;
			//busca o total de comentários do post
			$com->get(array("count(*) com"),"id_post=$post->id_post");
			$com->result();
			$dados[num_com][$i] = $com->com;
			$i++;
		}
		//invoca o método que constroi a visão
		app::showView("view/index_view.php",$dados); //chama o template
	}
}
