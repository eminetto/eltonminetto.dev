<html> 
  <head><title> Micro local news </title> 
	<link rel="stylesheet" href="css/jquery.mobile-1.0a3.min.css" />
  </head> 
<body>

<script src="js/jquery-1.5.min.js"></script>
<script src="js/jquery.mobile-1.0a3.min.js"></script>
<div data-role="page"> 
	<div role="banner" class="ui-bar-b ui-header" data-role="header" data-theme="b">
		<h1 aria-level="1" role="heading" tabindex="0" class="ui-title">Conteúdos</h1>
	<a data-theme="b" title="Home" href="http://coderockr.dyndns.org:4080/geo/" data-icon="home" data-iconpos="notext" data-direction="reverse" class="ui-btn-right jqm-home ui-btn ui-btn-up-b ui-btn-icon-notext ui-btn-corner-all ui-shadow"><span class="ui-btn-inner ui-btn-corner-all"><span class="ui-btn-text">Home</span><span class="ui-icon ui-icon-home ui-icon-shadow"></span></span></a>
	</div>

<div data-role="content">
<?php
//mysql 
/*** mysql hostname ***/
$hostname = 'localhost';

/*** mysql username ***/
$username = 'root';

/*** mysql password ***/
$password = 'cr123456';

try {
    $dbh = new PDO("mysql:host=$hostname;dbname=geo", $username, $password);
    /*** echo a message saying we have connected ***/
    }
catch(PDOException $e)
    {
    echo $e->getMessage();
}
$place_id = $_GET['place_id'];
$sql = "select * from content where place_id = $place_id order by title";
$stmt = $dbh->prepare($sql);
$stmt->execute();
?>

<?php
while ($row = $stmt->fetch()) {
	if($row['type'] == 'Ad') 
		echo '<div class="ui-body ui-body-e">';
	else 
		echo '<div class="ui-body ui-body-c">';
	echo '<h2>', $row['title'],'</h2>';
	echo nl2br($row['text']), '<br>';
	echo '</div>';
}
?>
<!--
<form id="newContent">
	<fieldset>
	<div data-role="fieldcontain" action="submit.php">
		<input type="hidden" name="place_id" value="<?php echo $place_id;?>">
		<label for="textarea">Sua opinião:</label>
		<textarea cols="40" rows="8" name="textarea" id="textarea"></textarea>
		<button type="button" data-theme="b" name="submit" value="submit-value" class="ui-btn-hidden" >Enviar</button>
	</div>
	</fieldset>
</form>

</div>
-->

</div>
<?php
/*

<script>
	$('#enviar').click(function(){
		//$.post("submit.php", newContent.serialize(), function(data){ alert('Salvo');}
		
		$.post( 
			$('form#newContent').attr('action'), 
			$('form#newContent').serialize(), 
			function(data){ 
				if( data.success ){
					//window.location.href = '<?php echo Zend_Registry::get('serverPhp') ?>/cadastro/sucesso/session_id/' + data.session_id
					alert('salvo');
				}else{
					alert('erro');
				} 
			}, 
			'json'
		);
	);
</script>
*/
?>
</body>
</html>