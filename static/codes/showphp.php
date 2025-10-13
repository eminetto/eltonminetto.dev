<?
$file = $_GET[file];
$extensao = strtolower(end(explode('.', $file)));
if($extensao != 'php' && $extensao != 'html' && $extensao != 'htm' && $extensao != 'css' && $extensao != 'js') {
	echo "Somente arquivos php";
	exit;
}
highlight_file($file);

?>
