---
categories:
- e-book
comments: true
date: 2012-11-29T15:06:12Z
slug: escrevendo-um-livro-do-modo-nerd
title: Escrevendo um livro do modo nerd
url: /2012/11/29/escrevendo-um-livro-do-modo-nerd/
wordpress_id: 1206
---

Recentemente lancei meu segundo e-book, o [Zend Framework 2 na prática](http://www.zfnapratica.com.br). Uma grande diferença entre este e o primeiro foi a forma como escrevi o livro.
<!--more-->
O primeiro e-book eu usei uma forma mais tradicional: escrevi no Pages (concorrente da Apple para o Microsoft Word) no Mac e depois exportei para PDF e epub (funções nativas do aplicativo). As vantagens dessa abordagem são conhecidas, como a facilidade de acompanhar visualmente o resultado final, corretor ortográfico, etc. A primeira desvantagem que encontrei foi na hora de formatar os códigos, para deixá-los coloridos e com fonte mono-espaçada. Outro problema foi ter que usar uma ferramenta extra para gerar a versão mobi compatível com o Kindle da Amazon.

No segundo livro usei uma abordagem mais "nerd". Usei um serviço chamado [Leanpub](http://www.leanpub.com) que conheci graças a um tweet do [@alganet](http://twitter.com/alganet). O Leanpub funciona da seguinte forma: após um rápido cadastro, do usuário e do livro, é solicitada permissão para acessar sua conta no Dropbox. Com o acesso permitido é criada uma pasta compartilhada com o usuário do Leanpub. Nessa pasta você vai escrever o texto do seu livro, cada capítulo em um arquivo separado, no formato [Markdown](http://pt.wikipedia.org/wiki/Markdown). O Markdown é um formato leve, somente texto, que pode ser facilmente processado e gerar conteúdos em HTML ou o que o usuário desejar. Neste caso o Leanpub processa o Markdown e gera o livro nos formatos PDF, ePub e mobi, não necessitando nenhum passo extra.
Algumas vantagens que encontrei usando essa combinação:

	
* Agilidade na escrita. Por não precisar me preocupar com a formatação final eu fiquei focado apenas no conteúdo

	
* Independência de ferramentas. O conteúdo agora é no formato texto, não dependendo de uma ferramenta de edição como o Pages ou o Word. Posso alterar o conteúdo usando qualquer editor de texto em qualquer plataforma. No Mac eu usei uma ferramenta chamada [Mou](http://mouapp.com) e no iPad um aplicativo gratuíto, o [Nocs](https://www.google.com.br/url?sa=t&rct=j&q=markdown+ipad+nocs&source=web&cd=1&ved=0CDAQFjAA&url=https%3A%2F%2Fitunes.apple.com%2Fus%2Fapp%2Fnocs-markdown-dropbox-your%2Fid396073482%3Fmt%3D8&ei=hZK3UNm_NIGQ8wTc6oGoCA&usg=AFQjCNEApeXufEr7IS0MkLCXm6X-mZJlXA)

	
* Formatação automática dos códigos. Basta usar uma tag do Markdown para especificar que determinado trecho é um código PHP, por exemplo, e o Leanpub gera as fontes coloridas e formatadas.


O Leanpub é uma ferramenta gratuíta mas ganha dinheiro graças a uma plataforma de venda de livros que eles disponibilizam. Ao final da escrita do livro é possível usar a plataforma deles para fazer a venda, com cobrança pelo Paypal. Neste caso eles ficam com uma pequena porcentagem das vendas do livro, exatamente como qualquer outra loja, como a Amazon ou a iBooks Store. Eu não estou usando essa opção pois optei por usar a ferramenta que havia criado para a venda do primeiro e-book, usando o Pagseguro.

Para completar o pacote nerd eu usei um repositório privado no Bitbucket para fazer o controle de versão dos textos e imagens usadas no livro. Assim eu tenho um histórico e backup das alterações conforme vou escrevendo o livro.

Gostei muito do processo de escrita usando essas ferramentas, tanto o uso do Markdown quanto a versão final gerada pelo Leanpub e já estou planejando o próximo e-book escrito desta forma para 2013.



