---
categories: null
comments: true
date: 2015-05-10T00:00:00Z
title: Usando o Doctrine Second Level Cache
url: /2015/05/10/usando-o-doctrine-second-level-cache/
---

Com o lançamento da versão 2.5 do Doctrine uma feature há muito esperada tornou-se estável o suficiente para uso. Trata-se do "Second Level Cache", que segundo a [documentação oficial](http://doctrine-orm.readthedocs.org/en/latest/reference/second-level-cache.html) foi projetada para reduzir a quantia de acessos a base de dados. Ele fica entre a aplicação e a base de dados para evitar o maior número possível de acessos ao banco. 

Quando habilitado todas as entidades são primeiro pesquisadas no cache e caso não sejam encontradas são realizadas consultas e o valor da mesma fica em cache, para reduzir o acesso ao banco de dados nas próximas requisições. 

O primeiro passo para usar o second level cache é ter certeza que estamos usando a versão 2.5 ou maior do Doctrine. Para isso alteramos nosso composer.json para algo parecido com:

```javascript
{
    "require": {
        "doctrine/common": "2.5.*",
        "doctrine/dbal": "2.5.*",
        "doctrine/orm": "2.5.*"
    }
}
```

Lembre-se de executar o composer update para atualizar o vendor do projeto. 

Agora podemos habilitar o cache no bootstrap do projeto na criação do EntityManager. 

```php

// Enable second-level-cache
//pode ser qualquer outra forma de cache como Memcached, Xcache, etc
$cache = new \Doctrine\Common\Cache\ApcCache;
$cacheRegionConfiguration = new \Doctrine\ORM\Cache\RegionsConfiguration();
$factory = new \Doctrine\ORM\Cache\DefaultCacheFactory($cacheRegionConfiguration, $cache);
$config->setSecondLevelCacheEnabled();
$config->getSecondLevelCacheConfiguration()->setCacheFactory($factory);

```

O código completo pode ser visto nesse [gist](https://gist.github.com/eminetto/e66bd041328eadd87db5)

O próximo passo é configurar nossas entidades para que elas façam uso do cache. Podemos escolher um dos modos de cache disponíveis:

- READ\_ONLY, o valor padrão. Permite que as entidades em cache sejam apenas lidas e não modificadas. É a forma mais performática mas é útil apenas para entidades que são apenas para leitura
- NONSTRICT\_READ\_WRITE. Permite que as entidades possam ser alteradas mas não possui controle de "lock".
- READ\_WRITE. O modo mais completo, permite alterações e faz um controle mais seguro do acesso as entidades evitando conflitos. Mas para isso perde um pouco da performance e o sistema de cache selecionado precisa permitir o recurso de locks. 

Você agora pode configurar cada uma das suas entidades para usar o melhor modo de cache, como no trecho abaixo:

```php
<?php
namespace DoctrineNaPratica\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="User")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class User
{

```

Neste exemplo foi escolhido o modo NONSTRICT\_READ\_WRITE e o cache vai permitir que as entidades sejam alteradas normalmente. Quando uma alteração é realizada a entidade é persistida do banco de dados e também alterada automaticamente no cache, de forma que as próximas requisições possam acessá-la sem a realização de uma nova consulta no banco de dados. 

Na imagem abaixo é possível ver o cache funcionando no painel do [Z-Ray](http://eltonminetto.net/blog/2015/01/12/usando-o-z-ray-com-o-zend-framework/)

[![](/images/posts/second-level-cache.png)](/images/posts/second-level-cache.png)

Na documentação do Doctrine é possível ver outras funcionalidades como adicionar um log, alterar o tempo de vida do cache ou mesmo realizar a limpeza do mesmo.

Realmente uma funcionalidade que permite aumentar muito a performance de projetos usando este excelente ORM.

P.S.: este post é uma continuação do capítulo sobre performance do livro Doctrine na prática que pode ser adquirido no [Leanpub](https://leanpub.com/doctrine-na-pratica)