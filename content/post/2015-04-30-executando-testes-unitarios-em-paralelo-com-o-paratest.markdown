---
categories: null
comments: true
date: 2015-04-30T00:00:00Z
title: Executando testes unitários em paralelo com o paratest
url: /2015/04/30/executando-testes-unitarios-em-paralelo-com-o-paratest/
---

Não consigo contar o número de horas economizadas graças ao uso de testes unitários e todo o conceito de TDD. Mas o número de horas que eu gastava na execução de toda a suite de testes estava me irritando, por isso pesquisei por uma forma de melhorar este processo.

O primeiro passo foi otimizar ao máximo os códigos e estrutura dos testes mas chegou a um ponto que não consegui aumentar a performance apenas desta forma. Este é o momento que podemos partir para o próximo estágio, que é executar os testes em paralelo. 

Encontrei duas ferramentas para executar esta tarefa, a [PHPUnit Cluster Runner](http://www.clusterrunner.com/) e o [paratest](https://github.com/brianium/paratest). O _ClusterRunner_ me parece bem mais completo e complexo, mas o _paratest_ resolveu meu problema. 

Foi preciso apenas alterar o _composer.json_ do projeto para incluir:

``` php
"require": {
    "brianium/paratest": "dev-master"
}
```

e atualizar o vendors com o _composer update_.

Ao executar o comando _./vendor/bin/paratest_ é possível ver o seu help. As opções mais importantes são a -p que serve para indicar o número de processos e o -c para indicar onde está seu _phpunit.xml_ com a configuração da sua suite de testes.

O que o _paratest_ faz é criar um processo PHP para cada arquivo de sua suite de testes e executá-los em paralelo. 

Rodando um _ps -aux | grep php_ é possível ver os processos executando, algo como:

	php vendor/bin/phpunit --configuration tests/phpunit.xml Api\Controller\RestControllerTest module/Api/tests/src/Api/Controller/RestControllerTest.php

Você pode usar o comando -f para que cada processo execute um teste apenas, um método de cada arquivo de teste. Ao rodar o _ps -aux | grep php_ você deve ver algo parecido com isso:

	php vendor/bin/phpunit --configuration tests/phpunit.xml --filter /testGetListNotFound(?:\s|$)/  module/Api/tests/src/Api/Controller/RestControllerTest.php

Como resultado do _paratest_ eu consegui rodar um conjunto de testes grande, que estava demorando 17.67 minutos em apenas 4.91 minutos, usando 5 processo em paralelo. 

Um ponto interessante foi que na primeira execução do _paratest_ encontrei alguns testes que funcionavam perfeitamente quando executados em sequência, mas que davam erro ao serem executados em paralelo. Ou seja, não estavam seguindo corretamente o conceito de que cada teste não deve influenciar ou depender de outro.

Desta forma o _paratest_ serve tanto para aumentar a performance da execução dos testes, algo muito útil em um ambiente de integração contínua, quanto como uma forma extra de validar seus testes.

