---
categories:
- php
comments: true
date: 2013-07-03T00:00:00Z
title: 'Atoum: uma alternativa ao PHPUnit'
url: /2013/07/03/atoum-uma-alternativa-ao-phpunit/
---

Pesquisando novas ferramentas de testes e integração contínua, em especial o [CircleCI](https://circleci.com]), acabei encontrando um projeto interessante, o [atoum](https://github.com/atoum/atoum). O projeto tem uma ambição grande: ser uma alternativa ao padrão do mercado, o PHPUnit. 

O atoum é baseado nas novas features do PHP 5.3 e foi desenvolvido com as seguintes idéias:

*  Pode ser implementado rapidamente ;
* Desenvolvimento simplificado de testes;
* Permitir a criação de testes legíveis, confiáveis e claros;

Para poder comparar com o PHPUnit eu fiz um pequeno projeto de [exemplo](https://github.com/eminetto/post_atoum). 

<!--more-->

# Instalação

Tanto o PHPUnit quanto o atoum possuem mais de uma opção de instalação, mas eu optei por usar o [Composer](http://code-squad.com/screencast/composer), que deveria ser a forma oficial de instalação de qualquer projeto moderno.  Para isso, primeiro instalei o Composer usando o comando:

	curl -sS https://getcomposer.org/installer | php

No arquivo _composer.json_ vamos incluir os dois frameworks:

``` javascript
{
    "require": {
        "atoum/atoum": "dev-master",
        "phpunit/php-timer": "1.0.4",
        "phpunit/phpunit-mock-objects": "1.2.*@dev",
        "phpunit/php-code-coverage": "1.2.*@dev",
        "phpunit/phpunit": "3.7.*@dev"
    }
}
``` 

E executar o comando:
	
	php composer.phar install

# Classe

O próximo passo foi criar uma classe para ser testada, no diretório _src\Service\Auth.php_:

``` php
<?php
namespace Service;

class Auth
{

	const INVALID_USER = 1; 
	const INVALID_PASSWORD = 2; 
	const VALID_AUTH = 3; 

	private $pdo;

	public function __construct(\PDO $pdo) 
	{
		$this->pdo = $pdo;
	}

	public function authenticate($login, $password)
	{
		//todo: filter parameters!
    	$sth = $this->pdo->prepare('select * from user where login = ?');
		$sth->bindParam(1, $login, \PDO::PARAM_STR);
		$sth->execute();
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if (! $result) {
			return $this::INVALID_USER;
		}
		if ($password != $result['password']) {
			return $this::INVALID_PASSWORD;
		}
	
		return $this::VALID_AUTH;
	}
}
```

# PHPUnit

Para facilitar a comparação, fiz primeiro o teste com a ferramenta que conheço, o [PHPUnit](http://phpunit.de).

Para isso criei o diretório _tests\phpunit_ e criei dentro dela os seguintes arquivos e diretórios:

	Service\AuthPHPUnitTest.php
	bootstrap.php
	phpunit.xml

O _bootstrap.php_ e o _phpunit.xml_ são arquivos auxiliares, sendo que no primeiro apenas configuro o _loader_ do _Composer_ e o segundo é o arquivo de configurações dos testes, com a inclusão do _Code Coverage_. 

``` php
<?php

$loader = require __DIR__.'/../../vendor/autoload.php';
$loader->add('Service', __DIR__.'/../../src');
```

``` xml
<phpunit
    bootstrap="bootstrap.php"
    colors="true"
    backupGlobals="false"
>
    <testsuites>
        <testsuite name="Test Suite">
            <directory>./Service</directory>
        </testsuite>
    </testsuites>

    <!-- Code Coverage Configuration -->
    <filter>
        <whitelist>
            <directory suffix=".php">../../src/</directory>
            <exclude>
                <directory suffix=".php">./</directory>
            </exclude>
        </whitelist>
    </filter>

    <logging>
        <log type="coverage-html" target="_reports/coverage" title="Coverage" charset="UTF-8" yui="true" highlight="true" lowUpperBound="35" highLowerBound="70"/>
        <log type="coverage-clover" target="_reports/logs/clover.xml"/>
        <log type="junit" target="_reports/logs/junit.xml" logIncompleteSkipped="false"/>
        <log type="testdox-text" target="_reports/testdox/executed.txt"/>
    </logging>
</phpunit>
```
No _AuthPHPUnitTest.php_ estão os testes:

``` php
<?php

use Service\Auth;

class AuthPHPUnitTest extends \PHPUnit_Framework_TestCase
{

	private $validUser = 'kratos';
	private $validPassowrd = '1ca308df6cdb0a8bf40d59be2a17eac1';
    private $pdo;

	/**
     * Faz o setup dos testes
     * @return void
     */
    public function setup()
    {
        parent::setup();
        $this->pdo = new \PDO('sqlite:memory');
        $this->pdo->query('create table user (id INTEGER PRIMARY KEY AUTOINCREMENT, login text, password text)');
        $sth = $this->pdo->prepare('insert into user values(null,?,?)');
        $sth->bindParam(1, $this->validUser, \PDO::PARAM_STR);
        $sth->bindParam(2, $this->validPassowrd, \PDO::PARAM_STR);
        $sth->execute();
    }

    public function testInvalidUser() 
    {
    	$auth = new Auth($this->pdo);
    	$result = $auth->authenticate('invalidUser', $this->validPassowrd);

    	$this->assertEquals($result, Auth::INVALID_USER);
    }

    public function testInvalidPassword() 
    {
    	$auth = new Auth($this->pdo);
    	$result = $auth->authenticate($this->validUser, 'invalidPassword');

    	$this->assertEquals($result, Auth::INVALID_PASSWORD);
    }

    public function testValidAuth() 
    {
    	$auth = new Auth($this->pdo);
    	$result = $auth->authenticate($this->validUser, $this->validPassowrd);

    	$this->assertEquals($result, Auth::VALID_AUTH);
    }
}
``` 

Para executar os testes basta executar os comandos:

	cd tests/phpunit/
	../../vendor/bin/phpunit

E o resultado será apresentado no console. Se você tiver o [XDebug](http://xdebug.org) instalado e configurado em seu PHP será criado um diretório chamado __reports_  com o relatório da cobertura de código da sua classe, o Code Coverage.

# atoum

Vamos agora criar e configurar o atoum. 

Criei um diretório para armazenar os testes, o _tests\units_  e dentro dele criei a seguinte estrutura:

	Service\Auth.php
	bootstrap.php
	coverage.php
	
O _bootstrap.php_ e o _coverage.php_ são, respectivamente o arquivo de bootstrap dos testes e a configuração da cobertura de códigos:

```php
<?php
//bootstrap.php
$loader = require __DIR__.'/../../vendor/autoload.php';
$loader->add('Service', __DIR__.'/../../src');
```
```php
<?php
//coverage.php
use \mageekguy\atoum;

$coverageHtmlField = new atoum\report\fields\runner\coverage\html('Your project name', '_reports');
$coverageHtmlField->setRootUrl('http://url/of/web/site');
$coverageTreemapField = new atoum\report\fields\runner\coverage\treemap('Your project name', '_reports');
$coverageTreemapField
	->setTreemapUrl('http://url/of/treemap')
	->setHtmlReportBaseUrl($coverageHtmlField->getRootUrl())
;
$script
	->addDefaultReport()
		->addField($coverageHtmlField)
		->addField($coverageTreemapField)
;
```

E o _Auth.php_ contém os testes:

```php
<?php
namespace tests\units\Service;

include __DIR__ . '/../bootstrap.php';

use mageekguy\atoum\test;
use Service\Auth as AuthService;
use mageekguy\atoum\reports;

class Auth extends test
{
	private $validUser = 'kratos';
	private $validPassowrd = '1ca308df6cdb0a8bf40d59be2a17eac1';
    private $pdo;

    public function beforeTestMethod($testMethod) {
        $this->pdo = new \PDO('sqlite:memory');
        $this->pdo->query('create table user (id INTEGER PRIMARY KEY AUTOINCREMENT, login text, password text)');
        $sth = $this->pdo->prepare('insert into user values(null,?,?)');
        $sth->bindParam(1, $this->validUser, \PDO::PARAM_STR);
        $sth->bindParam(2, $this->validPassowrd, \PDO::PARAM_STR);
        $sth->execute();
    }

    public function testInvalidUser()
    {
 		$auth = new AuthService($this->pdo);
    	$result = $auth->authenticate('invalidUser', $this->validPassowrd);
    	$this->assert->integer($result)
    				 ->isEqualTo(AuthService::INVALID_USER);
    }

    public function testInvalidPassword() 
    {
    	$auth = new AuthService($this->pdo);
    	$result = $auth->authenticate($this->validUser, 'invalidPassword');

    	$this->assert->integer($result)
    				 ->isEqualTo(AuthService::INVALID_PASSWORD);
    }

    public function testValidAuth() 
    {
    	$auth = new AuthService($this->pdo);
    	$result = $auth->authenticate($this->validUser, $this->validPassowrd);

    	$this->assert->integer($result)
    				 ->isEqualTo(AuthService::VALID_AUTH);
    }
}
```

Para executar os testes:

	cd tests/units
	../../vendor/atoum/atoum/bin/atoum -c coverage.php Service/Auth.php

No diretório __reports_ vai ser gerado o relatório em HTML da cobertura de códigos do seu projeto, desde que você tenha o XDebug instalado.

# Conclusões

A primeira diferença que percebi foi na hora de criar os testes. É obrigatório que o teste seja criado no _namespace tests\units_ e que o nome da classe de testes seja igual ao nome da classe que você está testando. No teste usando _PHPUnit_ o nome da classe que escrevi foi _AuthPHPUnitTest_ e para o _atoum_ foi preciso criar a classe com o nome _Auth_ ou o framework não rodava. 

Eu gostei do formato "fluido" dos testes do _atoum_ permitindo que você encadeie os _assertions_, ficando o código dos testes mais legíveis. Ele possui uma [boa biblioteca](https://github.com/atoum/atoum/tree/master/classes/asserters) de _assertions_ nativos para serem usados nos testes.

O _atoum_ também possui um [componente](https://github.com/jubianchi/PHPSandbox/blob/atoum-adapters-example/atoum-examples/content/Adapter.md) para fazermos _mocks_ mas não cheguei a testar nesse exemplo para poder chegar a uma conclusão sobre ele em comparação ao do _PHPUnit_ ou o _Mockery_. 

Quanto a performance, o _atoum_ me pareceu mais rápido do que o concorrente, mas como são poucos testes neste exemplo não consegui chegar a uma conclusão efetiva sobre isso.

O _atoum_ pode ser usado em conjunto com o Jenkins, mas eu achei o  relatório de _Code Coverage_ do _PHPUnit_ bem mais amigável. Existem documentações e projetos no Github que mostram como integrá-lo também ao Symfony e ao Zend Framework 2. 

Como o projeto foi desenvolvido por um francês boa parte da documentação ainda não foi traduzida para o inglês, muito menos para o português, mas no site oficial já existem bons exemplos e [textos](http://docs.atoum.org/). 

Ainda é cedo para dizer se o _PHPUnit_ corre algum risco, mas gostei muito do que vi e vou acompanhar a evolução do projeto de perto 


