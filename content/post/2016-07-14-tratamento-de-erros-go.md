+++
bigimg = ""
date = "2016-07-14T11:28:00-03:00"
subtitle = ""
title = "Tratamento de erros em Go"
url = "/2016-07-14-tratamento-erros-go"

+++

Um dos primeiros pontos que causam estranheza para quem está começando em Go é a forma como os erros são tratados, principalmente quando viemos de outras linguagens orientadas a objetos. Em Go os erros são "first class citizens", ou seja, eles não são ocultos ou delegados e são considerados parte importante do código. 
<!--more-->

Hoje passei por uma situação onde isso fez diferença. Revisando/debugando o código em PHP (alterado do original):

```php

<?php
$response = $this->transmitter->transmit($nfe, $xml);
$xml = $this->transmitter->getNfeWithTransmissionResults();
$result = $this->transmitter->saveData($response, $xml);
```

Assume-se que o _$response_, por exemplo, deve retornar um valor correto, que qualquer erro seria tratado pelo método _transmit_ e _exceptions_ seriam geradas caso contrário. 

Em Go o mesmo código poderia ser escrito da seguinte forma:

```go

response, err := transmitter.transmit(nfe, xml)
if err != nil {
    panic("Error ") //tratamento de erro qualquer
}
xml = transmitter.getNfeWithTransmissionResults()
result = transmitter.saveData(response, xml)

```

Como todo método pode retornar dois resultados, um de sucesso e outro de erro, podemos capturar e tratar o problema explicitamente. 

A primeira impressão é que o código está mais burocrático e ferindo algum princípio como os defendidos pelo SOLID, mas o erro e seu tratamento está bem mais claro. E em uma linguagem de programação que tem como foco o desenvolvimento de aplicativos concorrentes isso pode ser uma grande vantagem. 

Também é possível ignorar o erro usando: 

```go

response, _ := transmitter.transmit(nfe, xml)
xml = transmitter.getNfeWithTransmissionResults()
result = transmitter.saveData(response, xml)

```

E desta forma estamos delegando o controle para o método _transmit_, como no exemplo em PHP. 

A "moral da história" aqui é entender as vantagens e desvantagens de cada abordagem para podermos escolher a melhor forma para implementá-la em nossos projetos. 

Mais detalhes sobre o tratamento de erros em Go pode ser vista no [blog oficial](https://blog.golang.org/error-handling-and-go)