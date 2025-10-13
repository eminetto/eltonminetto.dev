<?
/* mostra o formulario de login*/
function mostraLogin() {
	session_start(); //inicia a sessão
	if(!$_SESSION[logado]) { //se ainda não foi feito a validação 
		app::showView("view/login_view.php"); //mostra a visão
	}
	else {
		blog::login(); //senão chama o metodo login()
	}
}
?>
