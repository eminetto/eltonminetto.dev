<?php
include("app.php"); //classe de configuracoes
include("tabela.php");

$tab = new tabela("pessoa"); //pessoa eh o nome da tabela  
/*inserindo dados */
$tab->codpes = 2;
$tab->nompes = 'elton luis minetto';
$tab->insert();
$tab->save();//realiza o commit

/* selecionando dados*/
$tab->get(array("*"));
while($tab->result()) {
	echo $tab->codpes."-".$tab->nompes."<br>";
}

/* atualizando dados */
$tab->nompes = 'Elton Luis Minetto';
$tab->update("codpes = 2");
$tab->save();

/* excluindo dados */
$tab->delete("codpes = 2");
$tab->save();
unset($tab);//é executado o destrutor
?>
