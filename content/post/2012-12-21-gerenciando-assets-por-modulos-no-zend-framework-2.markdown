---
categories:
- codes
- etc
- performance
- php
- Zend Framework
comments: true
date: 2012-12-21T14:44:59Z
slug: gerenciando-assets-por-modulos-no-zend-framework-2
title: Gerenciando assets por módulos no Zend Framework 2
url: /2012/12/21/gerenciando-assets-por-modulos-no-zend-framework-2/
wordpress_id: 1230
---

_Obs: Esse é um post avançado sobre Zend Framework 2. Se você não está familiarizado com os novos termos e conceitos do framework eu recomendo esse [screencast com a introdução](http://code-squad.com/screencast/introducao-zf2) ou o e-book [Zend Framework 2 na prática](http://www.zfnapratica.com.br) (eu sei que é cara de pau hehe)_

Uma das funcionalidades mais úteis do Zend Framework 2 é a forma como os módulos agora podem ser facilmente incluídos ou removidos de um projeto. Um módulo agora é realmente uma porção de código que pode ser reaproveitada facilmente. Nesse post vou mostrar isso usando um módulo muito útil chamado [AssetManager](https://github.com/RWOverdijk/AssetManager). 

<!--more-->
Este módulo, e diversos outros, pode ser encontrado no [repositório oficial de módulos do framework](http://modules.zendframework.com). No contexto desse post "assets" são arquivos estáticos como arquivos .js e .css.

O comportamento padrão de um projeto feito com o Zend Framework 2 é ter um diretório _public_ na raiz do projeto onde podemos salvar nossos arquivos públicos como js, css e imagens. Mas o problema com essa abordagem é que quando precisamos ter assets por módulos começa a ficar complexo de gerenciar, pois os arquivos estáticos não estão dentro do módulo e sim da pasta pública do projeto. Isso dificulta o processo de tornar um módulo independente do projeto, atrapalhando a portabilidade desse módulo.

O módulo AssetManager nos auxilia nesse processo pois adiciona a capacidade dos módulos possuírem sua própria pasta pública, totalmente indepententes do projeto.

Para esse post eu vou considerar que você já tem um projeto com o Zend Framework 2 funcionando. Caso não tenha eu recomendo que inicie pelo [projeto de exemplo](http://framework.zend.com/manual/2.0/en/user-guide/overview.html) da documentação oficial.

Com o projeto funcionando vamos incluir o novo módulo. Para isso alteramos o composer.json do projeto adicionando o novo módulo. O meu arquivo ficou assim:

``` javascript
{
    "name": "zendframework/skeleton-application",
    "description": "Skeleton Application for ZF2",
    "license": "BSD-3-Clause",
    "keywords": [
        "framework",
        "zf2"
    ],
    "homepage": "http://framework.zend.com/",
    "require": {
        "php": ">=5.3.3",
        "zendframework/zendframework": "2.*",
        "rwoverdijk/assetmanager": "*",
        "kriswallsmith/assetic": "1.1.*@dev"
    }
}
```

Foram adicionadas as linhas:

**"rwoverdijk/assetmanager": "*",
"kriswallsmith/assetic": "1.1.*@dev"
**

Que indicam o uso do módulo AssetManager e o projeto Assetic, que é usado pelo AssetManager.

Depois basta executar o comando:

```
php composer.phar update
```

Para que os pacotes sejam instalados no seu diretório vendor.

O primeiro passo é habilitar o módulo no nosso projeto alterando o arquivo /config/application.config.php:

``` php
return array(
   'modules' => array(
       'AssetManager',
       'Application',
     ),
    // ... outras configurações ...
```

Vamos agora criar a pasta pública do módulo Application, no diretório
/module/Application/public

E vamos criar um arquivo de exemplo dentro dele. O arquivo /module/Application/public/teste.css ficou assim:

``` css
body {
margin: 0;
padding: 0;
background-color: #2B454D;
text-align: center;
}
```

O último passo é alterar a configuração do módulo para que ele use o AssetManager. No arquivo /module/Application/config/module.config.php vamos adicionar:

``` php
'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
```

E podemos testar acezando no navegador: http://URL_PROJETO/teste.css

O arquivo vai ser processado normalmente. Na documentação do AssetManager consta as configurações avançadas para o [tratamento de conflitos](https://github.com/RWOverdijk/AssetManager/wiki/Resolvers) de nomes de arquivo entre módulos. Vale a pena uma lida com atenção para evitar problemas.


## Filtros


Outra funcionalidade interessante do AssetManager é a possibilidade de incluirmos filtros no processamento dos arquivos. Existem alguns filtros prontos e é simples criarmos outros mais especializados. Nesse exemplo vou mostrar o uso do JSMin, que vai minimizar os arquivos JS antes de os entregar ao navegador do usuário. Nesse contexto minimizar significa remover os espaços, comentários e quebras de linha para que o arquivo fique menor e seja entregue mais rapidamente.

Para ilustrar o funcionamento peguei um arquivo JS relativamente grande, o fonte do jQuery. Fiz download dele para a pasta pública do módulo com os comandos:

```
cd module/Application/public
wget http://code.jquery.com/jquery-1.8.3.js
```

Alterei o module.config.php para usar o filtro:

``` php
'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
        'filters' => array(
            'js' => array(
                array(
                    'filter' => 'JSMin',
                ),
            ),
        ),
    ),
```

Nesse exemplo estou mandando todos os arquivos com extensão .js para serem minimizados. Podemos minimizar somente alguns arquivos, como mostra a [documentação](https://github.com/RWOverdijk/AssetManager/wiki/Filters).

O AssetManager usa o projeto Assetic para executar os filtros, que por sua vez usa o próprio projeto JSMin. Infelizmente, no momento da escrita deste post, o JSMin não possui suporte ao Composer então precisamos fazer o download do mesmo manualmente. Fiz isso com os comandos, no diretório raiz do projeto:

```
mkdir -p vendor/mrclay/minify
cd vendor/mrclay/minify
wget https://raw.github.com/mrclay/minify/master/min/lib/JSMin.php
```

Precisamos também incluir o namespace do JSMin no loader do composer. Para isso alterei o arquivo init_autoloader.php na raiz do projeto:

``` php
if (file_exists('vendor/autoload.php')) {
	$loader = include 'vendor/autoload.php';
	$loader->add('JSMin', __DIR__ . '/vendor/mrclay/minify');
}
```

Podemos agora acessar pela url: http://URL_PROJETO/jquery-1.8.3.js e vamos ver o arquivo minimizado. É notável uma demora na entrega do arquivo pois o mesmo está sendo minimizado pelo JSMin.php. Vamos resolver essa demora nos próximos tópicos.

Outra boa prática para aumentar a performance de projetos web é diminuirmos o número de requisições ao servidor. Se tivermos 10 arquivos .css (ou .js) para entregar ao navegador a melhor forma é juntarmos os arquivos e enviarmos apenas um e não dez. O AssetManager facilita isso para nós. No module.config.php adicionamos a configuração de "collections":

``` php
'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'single.css' => array(
                    'teste.css',
                    'teste2.css',
                ),
            ),
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
        'filters' => array(
            'js' => array(
                array(
                    'filter' => 'JSMin',
                ),
            ),
        ),
    ),

```

Vamos criar o /module/Application/public/teste2.css com o conteúdo:

``` css
body,
input,
textarea,
button,
select
th,
td {
font-size: 14px;
}
```

Assim, acessando a url http://URL_PROJETO/single.css os dois arquivos são mesclados e apresentados como um só. Isso aumenta muito a velocidade para o cliente.


## Cache


Como vimos acima, ao usar o filtro ou a collection temos uma perda de performance pois os arquivos estão sendo processados antes de serem entregues. Podemos melhorar essa performance usando o recurso de cache. Desta forma os arquivos são processados, salvos em cache e entregues aos usuários. Sempre que o arquivo for requisitado o cache é consultado para verificar se já existe um resultado pronto e o mesmo é entregue caso exista.

Para isso basta alterar o module.config.php:

``` php
'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'single.css' => array(
                    'teste.css',
                    'teste2.css',
                ),
            ),
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
        'filters' => array(
            'js' => array(
                array(
                    'filter' => 'JSMin',
                ),
            ),
        ),
        'caching' => array(
            'default' => array(
                'cache'     => 'Apc',
            ),
        ),
    ),
```

Neste exemplo estou usando o Apc para armazenar o cache. Na [documentação](https://github.com/RWOverdijk/AssetManager/wiki/Caching) existem outros exemplos de armazenamento e regras avançadas, pois podemos desejar armazenar apenas alguns arquivos e não todos.

Na imagem abaixo é possível ver a diferença de performance entre a requisição da url http://URL_PROJETO/jquery-1.8.3.js sem o cache e com o mesmo. É visível o ganho de performance.

[![cache](/images/posts/cache_150.png)](/images/posts/cache.png)


## Conclusão


Este foi um post relativamente avançado principalmente pelos conceitos do Zend Framework 2, mas espero ter demonstrado a facilidade de trabalhar com módulos e a utilidade do AssetManager.
