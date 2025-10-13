---
categories:
- etc
- Zend Framework
comments: true
date: 2012-01-19T11:00:37Z
slug: usando-componentes-do-zend-framework
title: Usando componentes do Zend Framework
url: /2012/01/19/usando-componentes-do-zend-framework/
wordpress_id: 880
---

Uma das coisas mais legais do Zend Framework é a forma como ele foi construído, na forma de componentes que podem ser usados separadamente ou até substituídos. Dessa forma é possível usar somente alguns componentes em qualquer projeto, desenvolvido com outros frameworks ou mesmo sem nenhum. Exemplos de componentes que podem ser bem úteis:

- Zend_Mail
- Zend_Cache
- Zend_Db
- Zend_Config
- Zend_Date
- Zend_Log

Entre outros.
Um exemplo bem simples, usando o Zend_Cache:

``` php
<?php
//include do zend framework
$includePath  = get_include_path();
//o : é o separador de diretórios no Unix. No Windows seria ;
$includePath .= ':/var/www/html/library/';
set_include_path($includePath);

//inicia o autoloader, responsável por incluir os arquivos dos componentes
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);

$frontendOptions = array(
    'lifetime' => 7200, // tempo de vida
    'automatic_serialization' => true
);
$backendOptions = array('cache_dir' => '/tmp');
// criando uma instancia do cache
$cache = Zend_Cache::factory('Core',//frontend
    'File',  //backend
    $frontendOptions,
    $backendOptions
);

if(!$result = $cache->load('cachePosts')) {
      //aqui podemos usar o Zend_Db, por exemplo
      $result = 'aqui vai o processamento, como buscar os dados do banco';
      $cache->save($result, 'cachePosts');
}
echo $result;

```

Dessa forma é fácil de extender seu projeto usando componentes bem estruturados e testados.
