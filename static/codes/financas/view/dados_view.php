<table>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="13"><h2>Rendimentos</h2></td>
</tr>
<tr>
	<td></td>
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
<?
for($i=0;$i<count($cod_rend);$i++){
	?>
	<tr class="normal" id="rend<?=$categ_rend[$i]?>">
		<td><?=$desc_rend[$i]?>
		<div>
			<a href="javascript:delRendimento(<?=$cod_rend[$i]?>)">Excluir</a>
		</div>
		
		</td>
		<?
		for($x=1;$x<=12;$x++) {
			?>
			<td><input type="text" size="5" name="r_<?=$cod_rend[$i]?>_<?=$x?>" value="<?=$valor_rend[$i][$x]?>" onChange="atualizaRend(this)">			</td>
			<?
		}
		?>
	</tr>
	<?
}
?>
<tr>
	<td colspan="13">Categorias:
	<?
	foreach($categorias_rend as $c) {
		if($c) {
			?>
			&nbsp;<input type="checkbox" checked onclick="categorias('rend<?=$c?>')"><?=$c?>
			<?
		}
	}
	?>
	</td>
</tr>

<tr>
	<td colspan="13">
		<a href="javascript:mostraFormAddRend()">Adicionar rendimento</a><br>
		<div id="formAddRend" style="visibility:hidden; position:absolute">
			<form name="novoRend">
			Descrição: <input type="text" name="desc_rend"><br>
			Categoria: <input type="text" name="categ_rend"><br>
			<input type="button" value="Adicionar" onclick="insereRendimento()">&nbsp;
			<input type="button" value="Cancelar" onclick="escondeFormAddRend()">
			</form>
		</div>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="13"><h2>Despesas</h2></td>
</tr>
<tr>
	<td></td>
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
<?
for($i=0;$i<count($cod_desp);$i++){
	?>
	<tr class="normal" id="desp<?=$categ_desp[$i]?>">
		<td><?=$desc_desp[$i]?>
		<div>
			<a href="javascript:delDespesa(<?=$cod_desp[$i]?>)">Excluir</a>
		</div>
		
		</td>
		<?
		for($x=1;$x<=12;$x++) {
			?>
			<td><input type="text" size="5" name="d_<?=$cod_desp[$i]?>_<?=$x?>" value="<?=$valor[$i][$x]?>" onChange="atualizaDesp(this)">			</td>
			<?
		}
		?>
	</tr>
	<?
}
?>
<tr>
	<td colspan="13">Categorias:
	<?
	foreach($categorias_desp as $c) {
		if($c) {
			?>
			&nbsp;<input type="checkbox" checked onclick="categorias('desp<?=$c?>')"><?=$c?>
			<?
		}
	}
	?>
	</td>
</tr>
<tr>
	<td colspan="13">
		<a href="javascript:mostraFormAddDesp()">Adicionar despesa</a><br>
		<div id="formAddDesp" style="visibility:hidden; position:absolute">
			<form name="novoDesp">
			Descrição: <input type="text" name="desc_desp"><br>
			Categoria: <input type="text" name="categ_desp"><br>
			<input type="button" value="Adicionar" onclick="insereDespesa()">&nbsp;
			<input type="button" value="Cancelar" onclick="escondeFormAddDesp()">
			</form>
		</div>
	</td>
</tr>
</table>
