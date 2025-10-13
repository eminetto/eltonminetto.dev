<table>
<tr>
<?
foreach($anos as $ano) {
	if($ano == $_SESSION[ano])
		$css_class =  'negativo';
	else	
		$css_class =  'positivo';
	?>
	<td><span id="ano<?=$ano?>" class="<?=$css_class?>">--></span><a href="javascript:buscaAno(<?=$ano?>)"><?=$ano?></a></td>
	<?
}
?>
	<td>Adicionar ano: <input type="text" name="novoAno" onchange="insereAno(this)"></td>
</tr>
</table>
