---
categories:
- codes
- docs
- home
- python
comments: true
date: 2006-05-17T18:17:06Z
slug: padroes-algoritmicos-em-python
title: None
url: /2006/05/17/padroes-algoritmicos-em-python/
wordpress_id: 139
---

Um dos conteúdos que estou trabalhando na disciplina de Algoritmos e Estruturas de Dados III é Padrões Algoritmicos.

Dentre estes padrões existem os chamados "Algoritmos de Força Bruta (brute force algorithms)" e os "Algoritmos Gulosos (greedy algorithms)".

Segundo [1], "um  algoritmo de força bruta resolve um problema da maneira mais simples, direta e óbvia. Como resultado esse algoritmo terminar realizando muito mais trabalho para resolver um certo problema do que um algoritmo mais sofisticado. Por outro lado, um algoritmo de força bruta é em geral mais fácil de implementar do que um outro mais sofisticado e, por causa da sua simplicidade, às vezes ele é mais eficiente."

O mesmo autor sugere um exemplo de problema a ser resolvido usando essa abordagem, o problema da Contagem de Dinheiro. "Considere o problema que um caixa de banco resolve sempre que ele ou ela precisa dar ao cliente uma certa quantidade de dinheiro. O caixa tem à sua disposição um conjunto de várias notas e moedas de valores diferentes e precisa contar uma certa quantia usando o menor número possível de peças."

Um algoritmo usando a abordagem de força bruta iria testar todas as combinações possíveis até encontrar a melhor combinação. Fiz um código em Python para ilustrar esse algoritmo:

[forca_bruta.py](/codes/forca_bruta.py)
Executando o script:

_bash-3.00$ python forca_bruta.py
__Digite o valor a encontrar
20
Solucoes encontradas
Solucao [1, 1, 1, 1, 1, 15]
Solucao [10, 10]
A melhor solucao eh
[10, 10]_

O algoritmo encontrou duas soluções e sugeriu como a melhor a que usou menos moedas. Nesta abordagem ele testa todas as possibilidades até encontrar a melhor.

Já com a abordadem de algoritmos gulosos uma lógica mais sofisticada é utilizada. O caixa de um banco não iria testar todas as possibilidades até encontrar a melhor. Ele iria pegar as moedas com maior valor primeiro e iria encontrar uma solução para o problema. A idéia dos algoritmos gulosos é encontrar uma resposta não sabendo se é a melhor delas, mas torcendo para que seja. O algoritmo abaixo resolve o mesmo problema usando esta abordagem:

[guloso.py](/codes/guloso.py)
Sua execução retorna o seguinte:

_bash-3.00$ python guloso.py
Digite o valor a encontrar
20
Solucao
[15, 1, 1, 1, 1, 1]_
Ele mostra a primeira solução que encontrou. É mais rápido do que o força bruta e é uma solução correta, mas não é a melhor solução, que neste caso seria [10,10].

Mais um exemplo de python com ferramenta didática.

[1] [http://www.brpreiss.com/books/opus7/ ](http://www.brpreiss.com/books/opus7/)
