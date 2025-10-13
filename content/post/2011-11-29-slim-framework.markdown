---
categories:
- etc
- php
comments: true
date: 2011-11-29T12:02:14Z
slug: slim-framework
title: Slim Framework
url: /2011/11/29/slim-framework/
wordpress_id: 826
---

Alguns dias atrás estava preparando o material de um [curso](/blog/2011/09/30/tutorial-mao-na-massa-php-conference-2011/) que vou ministrar no PHPConference 2011. O assunto do curso já é bem "denso": Gearman, Memcached e Sphinx, então não queria aumentar a complexidade incluindo algum Framework, até porque o requisito do curso era apenas "conhecimentos em PHP".

Então iniciei o desenvolvimento do material usando somente o PHP e me deparei com a situação: "eu não consigo mais trabalhar sem frameworks!!". Parti então para a busca de um dos novos micro frameworks que surgiram nos últimos meses. Fiz uma pequena (micro?) pesquisa e encontrei algumas opções legais como o [Silex](http://silex.sensiolabs.org/), [DooPHP](http://doophp.com/), [Recess](http://www.recessframework.org/) e [Slim](http://www.slimframework.com/).

Após uma pequena análise acabei optando pelo Slim. O Silex é muito legal, pois é baseado no Symfony, mas não gostei de ter que usar o Twig para a parte de views, pois isso aumenta a complexidade. O DooPHP me pareceu faltar documentação. O Recess eu gostei bastante, mas vai ficar para um próximo estudo, principalmente a parte de REST, que achei interessante. O Slim me pareceu bem simples e a parte de views é feita por scripts PHP normais, sem precisar de um sistema de templates como o Twig ou Smarty (apesar de ser fácil integrá-los caso desejado).
Usando o exemplo básico do site é possível ver a simplicidade do Slim:

``` php
<?php
require 'Slim/Slim.php';
$app = new Slim();
$app->get('/hello/:name', function ($name) {
    echo "Hello, $name!";
});
$app->run();
?>
```
A idéia dele é fornecer apenas a parte de rotas e views simplificadas, sem se preocupar com a parte de modelos e coisas mais complexas.

**Organizando o projeto**

Com um pouco de esforço é possível fazer um projeto pequeno mas bem organizado usando-se o Slim. Abaixo a minha "receita" de projeto.

Estrutura de diretórios

```
    config
    controllers
    docs
    public
    	images
    	scripts
    	styles
    index.php
    vendors
    	Slim
    views
    
```


Conteúdos dos arquivos

.htaccess

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule !\.(js|ico|gif|jpg|png|css|htm|html)$ index.php
```

index.php

``` php
<?php
require 'vendors/Slim/Slim.php';
define('CONTROLLERS_PATH', './controllers/');
define('VIEWS_PATH', './views/');

$app = new Slim(array(
    'templates.path' => VIEWS_PATH
));
$controllerDir = opendir(CONTROLLERS_PATH); 
while ($controller = readdir($controllerDir)) {
	if($controller != '.' && $controller != '..')
		require CONTROLLERS_PATH . $controller;
}
$app->run();
``` 

Desta forma basta adicionar arquivos no diretório controllers e views. Um exemplo de controller e view

config/db.php

``` php
<?php
$dsn = 'mysql:host=localhost;port=3306;dbname=gallery';
$usuario = 'root';
$senha = '';
?>
```

controllers/Gallery.php

``` php
<?php
$app->get('/', function() use ($app) {
	require 'config/db.php';
	$pdo = new PDO($dsn, $usuario, $senha);
	$stmt = $pdo->query('SELECT * FROM gallery');
	$data = $stmt->fetchAll();
	$app->render('index.phtml', array('data' => $data));	
});

$app->get('/gallery/:id', function($id) use ($app) {
	require 'config/db.php';
	$pdo = new PDO($dsn, $usuario, $senha);
	$stmt = $pdo->query("SELECT * FROM gallery where id = $id");
	$gallery = $stmt->fetchAll();
	$stmt = $pdo->query("SELECT * FROM image where gallery_id = $id");
	$images = $stmt->fetchAll();
	$app->render('gallery.phtml', array('gallery' => $gallery, 'images' => $images));
});
?>
```

views/index.phtml

``` php
<h2>Galerias</h2>
<div id="gallery">
<?php if(isset($data)): ?>
	<?php foreach($data as $d): ?>
		<p><a href="/gallery/<?php echo $d['id'];?>"><?php echo $d['name'];?></a></p>
	<?php endforeach;?>
<?php endif;?>
</div>
```

views/gallery.phtml

``` php
<h2> <?php echo $gallery[0]['name']?></h2>
<h3><?php echo $gallery[0]['description']?></h3>
<?php foreach($images as $i): ?>
	
	<a href="/images/<?php echo $i['gallery_id'];?>/<?php echo $i['filename'];?>"  target="_blank">
		<img src="/images/<?php echo $i['gallery_id'];?>/<?php echo $i['filename'];?>200.png">
	</a>
<?php endforeach;?>
<p><a href="/">Voltar</a></p>
```

Claro que o Slim não substitui um framework completo como o Zend Framework ou o Symfony, mas para projetos pequenos, provas de conceito ou coisas mais simples ele é uma boa opção.

