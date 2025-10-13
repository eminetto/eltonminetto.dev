<?php
/** 
  * Classe financas
  * Elton Luis Minetto <eminetto at gmail dot com>
  * Licenca: GPL 
  */

session_start();
include("classes/app.php");
include("classes/tabela.php");

class financas extends app {
	//função inicial
	function index() {
		app::showView("view/index_view.php",$dados); //chama o template
	}
	
	//funcao que faz o login
	function login() {
		if(!$_SESSION[cod_usu]) {
			$tab = new tabela("usuario");
			$senha = md5($_POST[senha_usu]);
			$tab->get(array("*"),"email_usu='$_POST[email_usu]' and senha_usu='$senha'");	
			if($tab->result()) {
				$_SESSION[nome_usu] = $tab->nome_usu;
				$_SESSION[cod_usu] = $tab->cod_usu;
				app::showView("view/main_view.php",$dados); //chama o template
			}
		}
		else {
			app::showView("view/main_view.php",$dados); //chama o template
		}
	}
	
	//função que mostra os anos cadastrados
	function mostraAnos() {
		$tab = new tabela("anos");
		$tab->get(array("distinct ano"),"cod_usu=$_SESSION[cod_usu] order by ano");
		$i=0;
		while($tab->result()) {
			$dados[anos][$i] = $tab->ano;	
			$i++;
		}
		app::showView("view/anos_view.php",$dados); //chama o template
	}
	
	/* funcao que busca todos os rendimentos e despesas do ano*/	
	function mostraAno() {
		$_SESSION[ano] = $_GET[ano];
		//rendimentos
		$tab = new tabela("rendimentos");
		$meses = new tabela("rendimentos_mes");
		$tab->get(array("distinct rendimentos.cod_rend","rendimentos.desc_rend","categ_rend"),"cod_usu=$_SESSION[cod_usu] order by categ_rend,desc_rend");
		$i=0;
		while($tab->result()) {
			$dados[cod_rend][$i] = $tab->cod_rend;
			$dados[desc_rend][$i] = $tab->desc_rend;
			$dados[categ_rend][$i] = $tab->categ_rend;
			$meses->get(array("valor","mes"),"cod_usu=$_SESSION[cod_usu] and cod_rend = $tab->cod_rend and ano=$_GET[ano]");
			while($meses->result()) {
				$dados[valor_rend][$i][$meses->mes] = $meses->valor;
			}				
			$i++;
		}
		//categorias de rendimentos
		$tab->get(array("distinct categ_rend"),"cod_usu=$_SESSION[cod_usu]");
		$i=0;
		while($tab->result()) {
			$dados[categorias_rend][$i] = $tab->categ_rend;
			$i++;
		}
		//despesas
		$tab->setTabela("despesas");
		$meses->setTabela("despesas_mes");
		$tab->get(array("distinct despesas.cod_desp","despesas.desc_desp","categ_desp"),"despesas.cod_usu=$_SESSION[cod_usu]");
		$i=0;
		while($tab->result()) {
			$dados[cod_desp][$i] = $tab->cod_desp;
			$dados[desc_desp][$i] = $tab->desc_desp;
			$dados[categ_desp][$i] = $tab->categ_desp;
			$meses->get(array("valor","mes"),"cod_usu=$_SESSION[cod_usu] and cod_desp = $tab->cod_desp and ano=$_GET[ano]");
			while($meses->result()) {
				$dados[valor][$i][$meses->mes] = $meses->valor;
			}				
			$i++;
		}
		//categorias de despesas
		$tab->get(array("distinct categ_desp"),"cod_usu=$_SESSION[cod_usu]");
		$i=0;
		while($tab->result()) {
			$dados[categorias_desp][$i] = $tab->categ_desp;
			$i++;
		}
		app::showView("view/dados_view.php",$dados); //chama o template	
	}
	
	function atualizaSaldo() {
		//saldo
		//primeiro busca os rendimentos
		$tab = new tabela("rendimentos_mes");
		$tab->get(array("sum(valor) valor", "mes"),"cod_usu=$_SESSION[cod_usu] and ano = $_SESSION[ano] group by mes");
		while($tab->result()) {
			$rend[$tab->mes] = $tab->valor;
		}
		//busca as despesas para diminuir dos rendimentos
		$tab->setTabela("despesas_mes");
		$tab->get(array("sum(valor) valor", "mes"),"cod_usu=$_SESSION[cod_usu] and ano = $_SESSION[ano] group by mes");
		while($tab->result()) {
			$desp[$tab->mes] = $tab->valor;
		}		
		//calcula o saldo
		for($i=1;$i<=12;$i++) {
			$dados[saldo][$i] = $rend[$i] - $desp[$i];
		}
		app::showView("view/saldo_view.php",$dados); //chama o template		

	}
	
	function atualizaDesp() {
		$tmp = explode("_",$_GET[name]);
		$cod_desp = $tmp[1];
		$mes = $tmp[2];
		$valor = $_GET[value];
		$tab = new tabela("despesas_mes");
		$tab->valor = $valor;
		$tab->update("cod_usu = $_SESSION[cod_usu] and cod_desp=$cod_desp and ano=$_SESSION[ano] and mes=$mes");
		$tab->save();
	}	

	function atualizaRend() {
		$tmp = explode("_",$_GET[name]);
		$cod_rend = $tmp[1];
		$mes = $tmp[2];
		$valor = $_GET[value];
		$tab = new tabela("rendimentos_mes");
		$tab->valor = $valor;
		$tab->update("cod_usu = $_SESSION[cod_usu] and cod_rend=$cod_rend and ano=$_SESSION[ano] and mes=$mes");
		$tab->save();
	}	
	
	function insereDespesa() {
		$tab = new tabela("despesas");
		$tab->cod_usu = $_SESSION[cod_usu];
		$tab->desc_desp = $_GET[desc_desp];
		$tab->categ_desp = $_GET[categ_desp];
		$tab->insert();
		$tab->save();
		$tab->get(array("cod_desp"),"cod_usu=$_SESSION[cod_usu] and desc_desp='$_GET[desc_desp]'");
		$tab->result();
		$cod_desp = $tab->cod_desp;		
		for($i=1;$i<=12;$i++) {
			$tab->setTabela("despesas_mes");
			$tab->cod_usu = $_SESSION[cod_usu];
			$tab->cod_desp = $cod_desp;
			$tab->ano = $_SESSION[ano];
			$tab->mes = $i;
			$tab->insert();
		}
		$tab->save();			
	}
	
	function insereRendimento() {
		$tab = new tabela("rendimentos");
		$tab->cod_usu = $_SESSION[cod_usu];
		$tab->desc_rend = $_GET[desc_rend];
		$tab->categ_rend = $_GET[categ_rend];
		$tab->insert();
		$tab->save();
		$tab->get(array("cod_rend"),"cod_usu=$_SESSION[cod_usu] and desc_rend='$_GET[desc_rend]'");
		$tab->result();
		$cod_rend = $tab->cod_rend;		
		for($i=1;$i<=12;$i++) {
			$tab->setTabela("rendimentos_mes");
			$tab->cod_usu = $_SESSION[cod_usu];
			$tab->cod_rend = $cod_rend;
			$tab->ano = $_SESSION[ano];
			$tab->mes = $i;
			$tab->insert();
		}
		$tab->save();			
	}
	
	function insereAno() {
		$tab = new tabela("anos");
		$tab->cod_usu = $_SESSION[cod_usu];
		$tab->ano = $_GET[ano];
		$tab->insert();
		$tab->save();
		//insere as despesas e os rendimentos do ano como null
		$db = $tab->getCon();
		$db->BeginTrans( );
		$result = $db->Execute("insert into despesas_mes select distinct cod_usu,cod_desp,$_GET[ano],mes,null from despesas_mes where cod_usu=$_SESSION[cod_usu]");
		$result = $db->Execute("insert into rendimentos_mes select distinct cod_rend,cod_usu,$_GET[ano],mes,null from rendimentos_mes where cod_usu=$_SESSION[cod_usu]");
		$db->CommitTrans( );
	}
	
	function logout() {
		session_destroy();
		unset($_SESSION[cod_usu]);
		financas::index();
	}
	
	function cadastrar() {
		if(!$_POST[email_usu]) {
			app::showView("view/cadastrar_view.php");
		}
		else {
			/*$dados[valor][email] = $_POST[email_usu];
			$dados[valor][nome] = $_POST[nome_usu];
			if($_POST[senha_usu] != $_POST[senha_2]) {
				$dados[erro][senha] = 'As senhas não conferem';
				app::showView("view/cadastrar_view.php",$dados);
			}*/
			//insere o usuario
			$tab = new tabela("usuario");
			$tab->nome_usu = $_POST[nome_usu];
			$tab->senha_usu = md5($_POST[senha_usu]);
			$tab->email_usu = $_POST[email_usu];
			$tab->insert();
			$tab->save();
			//busca o codigo
			$tab->get(array("cod_usu"),"email_usu = '$_POST[email_usu]'");
			$tab->result();
			$cod_usu = $tab->cod_usu;
			$ano = date('Y');
			//insere o ano atual
			$tab->setTabela("anos");
			$tab->cod_usu = $cod_usu;
			$tab->ano = $ano;
			$tab->insert();
			$tab->save();
			//sessão
			$_SESSION[nome_usu] = $_POST[nome_usu];
			$_SESSION[cod_usu] = $cod_usu;
			$_SESSION[ano] = $ano;
			app::showView("view/main_view.php",$dados); //chama o template
		}
	}
	
	//funcao que faz a exclusão de um rendimento
	function delRendimento() {
		$tab = new tabela("rendimentos_mes");
		$tab->delete("cod_usu=$_SESSION[cod_usu] and cod_rend=$_GET[cod_rend]");
		$tab->save();
		$tab->setTabela("rendimentos");
		$tab->delete("cod_usu=$_SESSION[cod_usu] and cod_rend=$_GET[cod_rend]");
		$tab->save();
	}

	//funcao que faz a exclusão de uma despesa
	function delDespesa() {
		$tab = new tabela("despesas_mes");
		$tab->delete("cod_usu=$_SESSION[cod_usu] and cod_desp=$_GET[cod_desp]");
		$tab->save();
		$tab->setTabela("despesas");
		$tab->delete("cod_usu=$_SESSION[cod_usu] and cod_desp=$_GET[cod_desp]");
		$tab->save();
	}


}


$financas = new financas("mysql://financas:financas@192.168.1.250/financas?persist");

?>
