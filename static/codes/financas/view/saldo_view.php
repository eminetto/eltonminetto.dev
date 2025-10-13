<table>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
<td colspan="13"><h2>Saldos</h2></td>
</tr>
<tr>
	<td>Janeiro</td>
	<td>Fevereiro</td>
	<td>Março</td>
	<td>Abril</td>
	<td>Maio</td>
	<td>Junho</td>
	<td>Julho</td>
	<td>Agosto</td>
	<td>Setembro</td>
	<td>Outubro</td>
	<td>Novembro</td>
	<td>Dezembro</td>
</tr>
<tr>

<?
for($i=1;$i<=12;$i++){
	if($saldo[$i] < 0)
		$classe = 'negativo';
	else
		$classe = 'positivo';
	?>
	<td><p class="<?=$classe?>"><?=$saldo[$i]?></p></td>
	<?
}
?>
</tr>
</table>