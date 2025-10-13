+++
bigimg = ""
date = "2016-08-30T10:22:56-03:00"
subtitle = ""
title = "Gerando code coverage com PHPUnit e phpdbg"

+++

Em um [post anterior](http://eltonminetto.net/2016/04/08/melhorando-a-performance-do-phpunit/) eu mostrei alguns truques para identificar testes que estão demorando muito para serem executados. Neste texto vou mostrar uma forma de melhorar a performance da geração do relatório de cobertura de códigos usando o PHPUnit.

<!--more-->

É possível incluir configurações no arquivo _phpunit.xml_ para que sejam gerados relatórios relativos aos testes que estão sendo executados. Por exemplo:

```xml

<logging>
    <log type="coverage-clover" target="tests/_reports/logs/clover.xml"/>
    <log type="coverage-html" target="tests/_reports/coverage" charset="UTF-8" yui="true" highlight="true" lowUpperBound="35" highLowerBound="70" />
    <log type="testdox-text" target="tests/_reports/testdox/executed.txt"/>
</logging>
```

Desta forma será criado o diretório *tests/_reports* com uma série de informações úteis. No diretório *coverage-html* podemos ver detalhes da cobertura de testes dos códigos, facilitando a análise. O arquivo *clover.xml* é uma versão desta mesma informação para ser processada por serviços como Jenkins, CodeCov, Coveralls, Codacy, etc, para automatizarmos alertas e scripts. 

Para estas informações serem geradas além de alterarmos o *phpunit.xml* é necessário que instalemos a extensão *XDebug*. O problema é que ao fazermos isso temos uma queda considerável de performance. Confira o resultado da execução dos testes sem a geração dos relatórios:

```
eminetto@MacBook-Air (master) ~/Documents/Projects/planrockr/planrockr-backend: ./vendor/bin/phpunit 
PHPUnit 4.8.26 by Sebastian Bergmann and contributors.
Warning:	The Xdebug extension is not loaded
		No code coverage will be generated.

...............................................................  63 / 173 ( 36%)
............................................................... 126 / 173 ( 72%)
...............................................

Time: 1.08 minutes, Memory: 120.00MB

OK (173 tests, 682 assertions)
```

Habilitando o *XDebug* e rodando novamente teremos uma surpresa:

```
eminetto@MacBook-Air (master *) ~/Documents/Projects/planrockr/planrockr-backend: ./vendor/bin/phpunit 
PHPUnit 4.8.26 by Sebastian Bergmann and contributors.

...............................................................  63 / 173 ( 36%)
............................................................... 126 / 173 ( 72%)
...............................................

Time: 22.26 minutes, Memory: 128.00MB

OK (173 tests, 682 assertions)

Generating code coverage report in Clover XML format ... done

Generating code coverage report in HTML format ... done

```

O tempo de execução pulou de 1.08 para 22.26 minutos!

Depois de algumas pesquisas pela internet cheguei a [este post](http://blog.remirepo.net/post/2015/11/09/PHPUnit-code-coverage-benchmark) e resolvi testar o *phpdbg*.

Como estou usando o MacOS X para este teste eu executei os comandos abaixo para instalar todas as dependências que eu necessito.

```
brew install php70 --with-phpdbg
brew install php70-apcu
brew install php70-imagick
brew install php70-intl
brew install php70-mcrypt
brew install --HEAD homebrew/php/php70-memcached
brew install php70-mongodb
brew install php70-pdo-pgsql
brew install php70-xdebug
```

A diferença principal é o parâmetro *--with-phpdbg* usando na instalação do php7. 

Seguindo [este post](https://thephp.cc/news/2015/08/phpunit-4-8-code-coverage-support) do Sebastian Bergmann, criador do PHPUnit eu cheguei a sintaxe para rodar o teste novamente:

```
eminetto@MacBook-Air (master *) ~/Documents/Projects/planrockr/planrockr-backend: phpdbg -qrr ./vendor/bin/phpunit 
PHPUnit 4.8.26 by Sebastian Bergmann and contributors.

...............................................................  63 / 173 ( 36%)
............................................................... 126 / 173 ( 72%)
...............................................

Time: 1.59 minutes, Memory: 278.00MB

OK (173 tests, 682 assertions)

Generating code coverage report in Clover XML format ... done

Generating code coverage report in HTML format ... done

```

1.59 min é um tempo bem melhor do que os 22.26 usados pelo *XDebug*.

Na empolgação eu comentei isso no Twitter, antes mesmo de escrever este post:

[![twitter](/images/posts/twitter-derick.png)](/images/posts/twitter-derick.png) 

Se você observar, quem respondeu foi ninguém menos do que o criador do *XDebug*! Levando isso em conta fiz a comparação entre os resultados gerados pelo *XDebug* e o *phpdbg*. 

Abaixo a comparação do *coverage-html* gerado pelo *XDebug* (na esquerda) e o *phpdbg* (na direita da imagem).

[![coverage-html](/images/posts/coverage-html.png)](/images/posts/coverage-html.png) 

Usei a ferramenta CodeCov para processar o arquivo *clover.xml* e o resultado também foi ligeiramente diferente:

[![CodeCovCloverPHPDBG.png](/images/posts/CodeCovCloverPHPDBG.png)](/images/posts/CodeCovCloverPHPDBG.png)

[![CodeCovXdebug.png](/images/posts/CodeCovXdebug.png)](/images/posts/CodeCovXdebug.png) 

Segundo o relatório gerado pelo *phpdbg* o [Planrockr](http://planrockr.com) está com 66,05 % de cobertura de códigos. Já o *XDebug* apresenta o resultado de 65,92 %.

Algumas conclusões que posso tirar deste pequeno teste:

- O *XDebug* é uma ferramenta incrível e faz muito mais do que gerar cobertura de código, por isso não estou aqui dizendo que deveríamos parar de usá-la. Aqui estou apenas comparando um dos seus recursos
- Eu estou comparando o resultado "de fábrica", sem fazer ajustes em configurações do *XDebug* ou do *phpdbg*, por isso resultados diferentes podem acontecer em outros cenários
- Apesar da diferença de resultados entre os dois relatórios a diferença de performance compensa o uso do *phpdbg* no meu caso. 

Vou seguir usando o *phpdbg* por mais tempo e se algum novo resultado aparecer nas próximas semanas eu atualizo este post, ou gero outro relatando o aprendizado. 

E se eu estou errado em minhas conclusões por favor me avisem que terei o maior prazer em me retratar :)


