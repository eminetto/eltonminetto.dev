<?php
error_reporting(1);
/** 
  * Classe que define uma aplicacao
  * Elton Luis Minetto <eminetto at gmail dot com>
  * Licenca: GPL 
  * @package framework	
  */
class app {
	/**
	* string de conexao com a base de dados
	* @var string
	*/
	static $db_string = "";

	/**
	* Construtor da classe
	* @return void
	*/
	function __construct($string) {
		app::$db_string = $string;
		/**
		* O construtor eh resposavel por identificar se alguma funcao foi escolhida 
		* pelo usuario e chamar o metodo especifico
		* A variavel 'op' possui o nome da funcao escolhida pelo usuario 
		*/
		if(!$_REQUEST[op])
			$metodo = 'index'; //funcao padrao. deve estar definida na subclasse
		else
			$metodo = $_REQUEST[op];
		$classe =  get_class($this); //retorna o nome da classe atual. mesmo se for uma subclasse
		$ar = array($classe,$metodo); 
		call_user_func($ar); //executa o metodo chamado
		
	}
	
	
	/**
	* funcao que mostra a camada de visao
	* @param string $view O nome do arquivo.php que eh a visao
	* @param string[] $dados Os dados a serem trocados na visao
	* @return void
	*/
	function showView($view, $dados="") {
		if($dados)
			extract($dados); //transforma cada um dos indices do vetor de dados em variaveis
		include($view);
	}

}
