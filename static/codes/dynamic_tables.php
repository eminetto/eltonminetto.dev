<?php

/**
* Dynamic AJAX Tables
*
* Simple example to show how AJAX and tables can be combined to create
* a powerful inline table editor.  If you have any questions, please
* feel free to stop by labs.revision10.com.
*
* PHP versions 4 and 5
* Requires: sajax.php (http://absinth.modernmethod.com/sajax/)
*
* LICENSE: This work is licensed under a
* <a rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/">Creative Commons Attribution-NonCommercial 2.5 License</a>.
* You are free:
*    to copy, distribute, display, and perform the work
*    to make derivative works
* Under the following conditions:
*    Attribution. You must attribute the work in the manner specified by the author or licensor.
*    Noncommercial. You may not use this work for commercial purposes.
* For any reuse or distribution, you must make clear to others the license terms of this work.
* Any of these conditions can be waived if you get permission from the copyright holder.
* Your fair use and other rights are in no way affected by the above.
*
*
* @category   labs.revision10.com
* @package    dynamic ajax tables
* @author     Daren Jackson <daen@revision10.com>, alterado por Elton Minetto <emineto@gmail.com>
* @copyright  1997-2005 Revision10
* @link       http://labs.revision10.com/?p=5
*
*
* --
* -- Estrutura da tabela usada no exemplo
* --
*
* CREATE TABLE `pessoa` (
*   `codpes` int,
*   `nompes` varchar(200)
* );
*
*
*/

// Inicia sessão
session_start();

// Conexão com o banco
mysql_select_db('eminetto', mysql_connect('localhost', 'eminetto', 'elm2006net')) or die (mysql_error());

// Função principal que atualiza as informações da tabela
function changeText($sValue) {
    // decodifica os dados enviados
    $sValue_array = explode("~~|~~", $sValue);
    $sCell = explode("_", $sValue_array[1]);
    $parsedInput = htmlspecialchars($sValue_array[0], ENT_QUOTES);
    //Atualiza o banco
    if ($sCell[0]) { 
	//sCell[0] é o valor da chave da tabela
	//scell[1] é o nome do campo a ser atualizado
        $sql = mysql_query("UPDATE pessoa SET $sCell[1]= '$parsedInput' WHERE codpes = '$sCell[0]'");
    }
    // cria string para retornar para a página
    $newText = '<div onclick="editCell(\''.$sValue_array[1].'\', this);">'.$parsedInput.'</div>~~|~~'.$sValue_array[1];
    return $newText;
}

// sajax
require("Sajax.php");
$sajax_request_type = "POST";
sajax_init();
//$sajax_debug_mode = 1;
sajax_export("changeText");
sajax_handle_client_request();
?>

<html>
<head>
    <title>Tabelas Dinâmicas em Ajax</title>
<script type="text/javascript">
    <?
    sajax_show_javascript();
    ?>

    function textChanger_cb(result) {
        var result_array=result.split("~~|~~");
        document.getElementById(result_array[1]).innerHTML = result_array[0];
        Fat.fade_element(result_array[1], 30, 1500, "#EEFCC5", "#FFFFFF")
    }
    
    function parseForm(cellID, inputID) {
        var temp = document.getElementById(inputID).value;
        var obj = /^(\s*)([\W\w]*)(\b\s*$)/;
        if (obj.test(temp)) { temp = temp.replace(obj, '$2'); }
        var obj = /  /g;
        while (temp.match(obj)) { temp = temp.replace(obj, " "); }
        if (temp == " ") { temp = ""; }
        if (! temp) {alert("This field must contain at least one non-whitespace character.");return;}
        var st = document.getElementById(inputID).value + '~~|~~' + cellID;
        document.getElementById(cellID).innerHTML = "<span class=\"update\">Updating...</span>";
        x_changeText(st, textChanger_cb);
        document.getElementById(cellID).style.border = 'none';
    }

    function editCell(id, cellSpan) {
        var inputWidth = (document.getElementById(id).offsetWidth / 7);
        var oldCellSpan = cellSpan.innerHTML;
        
        document.getElementById(id).innerHTML = "<form name=\"activeForm\" onsubmit=\"parseForm('"+id+"', '"+id+"input');return false;\" style=\"margin:0;\" action=\"\"><input type=\"text\" class=\"dynaInput\" id=\""+id+"input\" size=\""+ inputWidth + "\" onblur=\"parseForm('"+id+"', '"+id+"input');return false;\"><br /><noscript><input value=\"OK\" type=\"submit\"></noscript></form>";
        document.getElementById(id+"input").value = oldCellSpan;
        document.getElementById(id+"input").focus();
        document.getElementById(id).style.background = '#ffc';
        document.getElementById(id).style.border = '1px solid #fc0';
    }
    
    function bgSwitch(ac, td) {
        if (ac == 'on'){
            td.style.background = '#ffc';
        } else if (ac == 'off'){
            td.style.background = '#ffffff';            
        }
    }    
</script>
    <link rel="Stylesheet" href="style.css" type="text/css" media="screen" />
    <script type="text/javascript" src="http://www.axentric.com/aside/fat/fat.js"></script>
</head>
<body >
<div id="datExample">
<table class="dynatab" border="0">
<tr>
    <th colspan="3">Pessoas</th>
</tr>
<tr class="yellow">
    <td>Código</td>
    <td>Nome</td>
</tr>
<?
    // Busca os dados na tabela
    $sql = @mysql_query("SELECT codpes, nompes FROM pessoa ORDER BY codpes");
    
    // monta a tabela
    while($row = mysql_fetch_array($sql)){stripslashes(extract($row));
        echo '<tr>';
        echo '<td>'.$codpes.'</td>';
	//o campo é formado pelo ValorDaChave_NomeDoCampo, exemplo: 1_nompes. Esse nome vai ser separado na função php no momento do update
        echo '<td class="point" id="'.$codpes.'_nompes" onmouseover="bgSwitch(\'on\', this);" onmouseout="bgSwitch(\'off\', this);"><div onclick="editCell(\''.$codpes.'_nompes\', this);">'.$nompes.'</div></td>';
        
        echo '</tr>';
    }
?>
</table>
</div>
</body>
</html>
