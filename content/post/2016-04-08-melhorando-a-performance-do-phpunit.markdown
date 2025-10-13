---
categories: null
comments: true
date: 2016-04-08T00:00:00Z
title: Melhorando a performance do PHPUnit
url: /2016/04/08/melhorando-a-performance-do-phpunit/
---

Em pleno 2016 acho que não preciso gastar caracteres comentando a importância dos TDD no desenvolvimento de software, porque você já está escrevendo testes, certo? 

O que eu vou comentar aqui é a importância deles executarem o mais rápido possível, porque se o processo de execução de testes for algo lento a tendência é o desenvolvedor escrever menos, ou executá-los esporadicamente. 
<!--more-->
Mas como identificar quais testes estão demorando mais e como melhorar a sua performance? Com estas dúvidas em mente comecei uma pesquisa que me levou às soluções que vou mostrar aqui. 

### Identificando quais testes são lentos

O primeiro passo é identificar quais testes estão demorando mais. Para isto vamos usar um recurso do *PHPUnit* chamado *Listeners*. Trata-se de uma configuração avançada do *phpunit.xml* onde indicamos um componente que vai ser executado junto com os testes e receber informação deles. O listener que vamos usar para isso chama-se *phpunit-speedtrap* e vamos começar instalando ele com o comando

	composer require johnkary/phpunit-speedtrap

 Após instalado vamos configurar o *phpunit.xml* para que o listener seja executado. Vamos incluir as linhas abaixo

```xml
<listeners>
	<listener class="JohnKary\PHPUnit\Listener\SpeedTrapListener" />
</listeners>
```

Ao executar o *phpunit* agora temos um novo resultado na saída dos testes, algo como:

```
You should really fix these slow tests (>500ms)...
 1. 3910ms to run Planrockr\Service\TeamTest:testWithoutMembers
 2. 3165ms to run Planrockr\Service\UserTest:testCreateUser
 3. 2764ms to run Planrockr\Service\UserTest:testCreateUserWithPromotion
 4. 2579ms to run Planrockr\Service\TeamTest:testDeleteTeamWithoutPermission
 5. 2453ms to run Planrockr\Service\TeamTest:testCreateWithFullData
 6. 2135ms to run Planrockr\DataSource\Github\UpdaterTest:testCreateBranch
 7. 2119ms to run Planrockr\DataSource\Github\UpdaterTest:testFork
 8. 2113ms to run Planrockr\Service\TeamTest:testDeleteTeam
 9. 2032ms to run Planrockr\Service\ProjectTest:testDelete
 10. 1781ms to run Planrockr\DataSource\Github\UpdaterTest:testPullRequestMerge
...and there are 128 more above your threshold hidden from view
```

O listener vai apontar quais são os 10 testes que estão demorando mais e quantos estão com problemas. É possível configurar o *phpunit.xml* para que o listener mostre mais ou menos de 10 testes e também alterar o limite de 500ms caso seja necessário. Mais detalhes podem ser vistos no [Github do projeto](https://github.com/johnkary/phpunit-speedtrap). 

### Identificando o motivo da lentidão

O *phpunit-speedtrap* ajuda bastante ao apontar quais testes estão lentos, mas ele não consegue nos dizer o motivo. Para isto podemos usar outro listener, o *phpunit/test-listener-xhprof*. Vamos começar instalando o componente com o composer:

	composer require phpunit/test-listener-xhprof lox/xhprof

Além do componente é necessário que a extensão do xhprof esteja instalada no PHP, o que pode ser visto no [site do projeto](http://xhprof.io) ou com os pacotes da sua distribuição Linux. 

Uma vez instalado vamos precisar configurar o phpunit.xml, incluindo as configurações abaixo dentro da tag <listener> :


```xml
<listener class="PHPUnit\XHProfTestListener\XHProfTestListener" file="vendor/phpunit/test-listener-xhprof/src/XHProfTestListener.php">
         <arguments>
          <array>
           <element key="appNamespace">
            <string>Planrockr</string>
           </element>
           <element key="xhprofWeb">
            <string>http://localhost:8888</string>
           </element>
           <element key="xhprofLibFile">
            <string>./vendor/lox/xhprof/xhprof_lib/utils/xhprof_lib.php</string>
           </element>
           <element key="xhprofRunsFile">
            <string>./vendor/lox/xhprof/xhprof_lib/utils/xhprof_runs.php</string>
           </element>
           <element key="xhprofFlags">
            <string>XHPROF_FLAGS_CPU,XHPROF_FLAGS_MEMORY</string>
           </element>
           <element key="xhprofIgnore">
            <string>call_user_func,call_user_func_array</string>
           </element>
          </array>
         </arguments>
        </listener>
```

A documentação com todas as opções que podem ser configuradas pode ser encontrada no [Github do projeto](https://github.com/phpunit/phpunit-testlistener-xhprof). Um ponto importante na configuração é o item *xhprofWeb* que está configurado com o endereço de um servidor web. Este servidor web nada mais é do que a interface web do xhprof e para usá-la basta executar os comandos:

	cd vendor/lox/xhprof/xhprof_html
	php -S localhost:8888

Ao executar o *phpunit* novamente o novo listener vai analisar cada um dos testes e gerar um gráfico com todas as chamadas sendo executadas dentro do teste, bem como quais estão demorando mais. O resultado da execução do *phpunit* agora vai ser algo como:

```
XHProf runs: 138
 * Core\Helper\TaskPullRequestTraitTest::testPercentages
   http://localhost:8888?run=57079ba289d44&source=Planrockr

 * Planrockr\DataSource\Trello\WeeklyMailTest::testProcess
   http://localhost:8888?run=57079ba4405dc&source=Planrockr

 * Planrockr\DataSource\Bitbucket\BitbucketTest::testSaveRepository
   http://localhost:8888?run=57079ba58da6f&source=Planrockr

 * Planrockr\DataSource\Bitbucket\UpdaterTest::testInstance
   http://localhost:8888?run=57079ba7040b3&source=Planrockr

```

Agora vamos unir o resultado dos dois listeners e melhorar os nossos testes. O *speedtrap* apontou que o pior teste era o 

	3910ms to run Planrockr\Service\TeamTest:testWithoutMembers

E o *test-listener-xhprof* gerou o resultado deste teste na url

```
  * Planrockr\Service\TeamTest::testWithoutMembers
http://localhost:8888?run=57079c1bc81cb&source=Planrockr
```

Acessando esta url é possível vermos a lista de métodos sendo invocados e quais estão lentos. O mais útil é o gráfico gerado:
[![callgraph](/images/posts/callgraph.png)](/images/posts/callgraph.png) 

Nele podemos ver em vermelho que o método *crypt* é o grande vilão deste teste. 

Após analisar alguns dos testes consegui melhorar a execução de toda a suite de 2.65 minutos para 38.53 segundos!

Uma observação. Ao usar os listeners a execução dos testes vai demorar um pouco mais, pois eles precisam analisar cada teste que está sendo efetuado. A dica é habilitar os listeners, fazer a análise, melhorar o que pode ser melhorado e desativá-los. E repetir o processo esporadicamente, ou mesmo automatizar isso para rodar em servidores de integração contínua. 

Outra dica legal é usar o [listener do Blackfire.io](https://blackfire.io/docs/integrations/phpunit) que permite usar asserts e fazer o teste falhar caso a performance seja maior do que determinado limite, entre outras features interessantes. 

Manter uma boa performance dos testes é algo tão importante quanto o desafio de manter a cobertura de código alta, pois são ambos sinais de melhoria da equipe e do projeto. 