<?
/* Elton Luís Minetto <eminetto@gmail.com>
Script para buscar noticias em RSS
*/

/*
Estrutura da tabela criada para armazenar as noticias

CREATE TABLE `rss` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(200) default NULL,
  `title` varchar(200) default NULL,
  `lido` int(11) default NULL,
  `date` datetime default NULL,
  `channel` varchar(200) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uk_title` (`channel`,`title`)
) TYPE=MyISAM AUTO_INCREMENT=30207 ;
*/

error_reporting(1);
include("mag/rss_fetch.inc");// download em http://magpierss.sourceforge.net/
$con = mysql_connect("localhost","eminetto","pass");
mysql_select_db("eminetto",$con);

if($_GET[op]=="lido") { //se foi clicado na opção de atualizar as noticias para lidas
	$sql = 	"update rss set lido=1";
	if($_GET[channel])
		$sql .= " where channel='$_GET[channel]'";
	$res = mysql_query($sql);
	topo();
	fim();	
	exit;

}
elseif($_GET[op]=="busca") { //busca
	topo();
	showRSS($_GET[query]);
	fim();
	exit;
	
}
//função que mostra o cabecalho
function topo() {
	?>
	<html>
	<head>
	<title>:: elm ::</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="old_site/site.css" type="text/css">
	</head>
	<body>
	<font size="3"><b>Notícias</b></font><br><br>
	<a href="noticias.php">[Atualizar]</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="noticias.php?op=lido">[Marcar todos como Lidos]</a><br><br>
	<form name="busca" action="noticias.php">
	<input type="hidden" name="op" value="busca">
	<input type="text" name="query"><input type="submit" value="Buscar">
	</form>
	<br>
	<DIV ID="waitDiv" style="position:absolute;left:200;top:200;visibility:hidden">
                <center>
                <table cellpadding=6 border=2 bgcolor=#ffffff bordercolor=#999999>
                <tr>
                        <td align=center>
                                </center>
                                <center>
                                <font color="#004080" face="Verdana, Arial, Helvetica, sans-serif" size="4">
                                <b>Carregando...</b></font><br>
                                <img src="await.gif" border="0"></center><br>
                                <font color="#004080" face="Verdana, Arial, Helvetica, sans-serif" size="2">Aguarde um instante...</font>
                        </td>
                </tr>
                </table>
                </center>
        </DIV>
	<script language="JavaScript">
	//Carregamento
	var DHTML = (document.getElementById || document.all || document.layers);
	function ap_getObj(name) {
	    if (document.getElementById) {
        	return document.getElementById(name).style;
	        }
        	else if (document.all) {
                	return document.all[name].style;
	        }
        	else if (document.layers) {
                	return document.layers[name];
	        }
	}
	function ap_showWaitMessage(div,flag)  {
        	if (!DHTML)
                	return;
	        var x = ap_getObj(div);
        	x.visibility = (flag) ? 'visible':'hidden'
	        if(! document.getElementById) {
        	        if(document.layers) {
                	        x.left=280/2;
                        	return true;
	                }
        	}
	}
	ap_showWaitMessage('waitDiv', 1);
	</script>
	<?
}

function fim() {
	?>
	<script language="javascript">
	    ap_showWaitMessage('waitDiv', 0);
	</script> 
	<?
}
//funcao que busca as noticias e insere na base de dados
function getrdf($url) {
	$rss = fetch_rss($url);
	if($rss) {
		$channel = $rss->channel['title'];
		foreach($rss->items as $item) {
			$title = $item[title];
			$url = $item[link];
			$res = mysql_query("insert into rss values(null,'$url','$title',0,sysdate(),'$channel')");
		}
	}	
}
//funcao que le as noticas na base de dados
function showRSS($query='') {
	if($query) 
		$sql = "select distinct channel from rss where upper(title) like upper('%$query%')";
	else
		$sql = "select distinct channel from rss where lido=0";
	$res = mysql_query($sql);
	while($db = mysql_fetch_object($res)) {
		echo '<br><img src="xml.png"><b>'.$db->channel.'</b>&nbsp;&nbsp;<a href="noticias.php?op=lido&channel='.$db->channel.'">[Marcar como Lido]</a><br>';
		if($query)
			$res1 = mysql_query("select * from rss where channel='$db->channel' and upper(title) like upper('%$query%')");
		else 
			$res1 = mysql_query("select * from rss where channel='$db->channel' and lido=0");
		while($db1 = mysql_fetch_object($res1)) {
			echo "<a href=$db1->url target=_blank>$db1->title</a>&nbsp;($db1->date)<br>";

		}
	}
}

	
topo();
//busca os arquivos dos sites de noticias
getrdf("http://people.ubuntulinux.org/~mako/ubuntu-traffic/rss.rdf");
getrdf("http://www.ajaxian.com/index.rdf");
getrdf("http://www.redhat.com/magazine/rss20.xml");
getrdf("http://info.abril.com.br/aberto/infonews/rssnews.xml");
getrdf("http://feeds.feedburner.com/gnomeTUX");
getrdf("http://schneider.blogspot.com/rss/blogger_rss.xml");
getrdf("http://slashdot.org/slashdot.rdf");
getrdf("http://www.pythonware.com/daily/rss.xml");
getrdf("http://www.planetpython.org/rss20.xml");
getrdf("http://www.gnomedesktop.com/backend.php");
getrdf("http://www.kde.org/dotkdeorg.rdf");
getrdf("http://www.underlinux.com.br/backend.php");
getrdf("http://br-linux.org/linux/?q=node/feed");
getrdf("http://www.redbooks.ibm.com/rss/ondemand.xml");
getrdf("http://newsforge.com/newsforge.rdf");
getrdf("http://lwn.net/headlines/rss");
getrdf("http://www.linuxsecurity.com.br/backend.php");
getrdf("http://freecode.linuxsecurity.com.br/backend.php");
getrdf("http://rssficado.com.br/xml.php?jb.xml");
getrdf("http://rssficado.com.br/xml.php?terra-noticias.xml");
getrdf("http://www2.mtv.com.br/drops/rssify.php");
getrdf("http://planet.ubuntulinux.org/rss20.xml");
getrdf("http://planet.gnome.org/rss20.xml");
getrdf("http://www.linhadecodigo.com.br/rss/artigos_geral.xml");
getrdf("http://grid.weblogsinc.com/rss.xml");
getrdf("http://planet.vivaolinux.com.br/rss20.xml");
//mostra na tela
showRSS();
mysql_close($con);
fim();
?>
</body>
