---
categories:
- codes
- docs
- home
comments: true
date: 2006-05-11T18:13:08Z
slug: miniframework-php
title: Miniframework PHP
url: /2006/05/11/miniframework-php/
wordpress_id: 131
---

Nas últimas semanas fiz uma pesquisa para encontrar um framework PHP que eu pudesse usar em meus aplicativos/sites. Encontrei vários frameworks interessantes como:



	
  * Symphony

	
  * Prado

	
  * Code Igniter

	
  * Zend Framework


Depois de ver vários deles uma pergunta me ocorreu: "realmente preciso de um framework?". Realmente utilizar um destes frameworks auxilia bastante o desenvolvimento, evitando que você refaça coisas que já existem. Mas muitas vezes estes frameworks possuem alguns problemas:

	
  * são muito maiores do que você precisa;

	
  * a documentação é confusa;

	
  * poucos estão suficientemente maduros;

	
  * é preciso aprender uma nova sintaxe ou maneira de se desenvolver;

	
  * você fica "engessado", é difícil fazer coisas mais avançadas ou que saem do padrão CRUD;


Então pensei que o que eu preciso é somente uma maneira de separar a lógica da apresentação, uma maneira de facilitar o acesso a bancos de dados e algo que me ajude a trabalhar com AJAX. Assim, peguei algumas idéias que vi nas documentações que li e algumas coisas que já utilizava e uni tudo para suprir minhas necessidades.

**Modelo de aplicação**

MVC Architecture?
Segundo [Rasmus Lerdorf](http://talks.php.net/show/yul/16), criador da linguagem PHP, "MVC é palavra do momento em arquiteturas de aplicação web. Ela vem do design de aplicações para desktop orientadas a eventos e não se aplica muito bem no design de aplicações web. Mas felizmente ninguém sabe exatamente o que MVC significa, então você pode chamar seu mecanismo de separação de camada de apresentação de MVC e seguir em frente."
Então, o que estou usando aqui é somente um mecanismo de separar o layout (html+css) do código PHP. Ao invés de usar as três camadas do MVC eu resumi para duas, Visão e Controle.
A parte da visão é representada por templates. Seguindo outra tendência que percebi em outros frameworks a parte de visão é representada por simples scripts PHP ao invés de utilizar uma das ferramentas de Templates como Fast Template, Smarty, etc. É mais simples de trabalhar e não é necessário aprender uma nova sintaxe.
Para a parte do controle eu desenvolvi uma classe chamada app. Nesta classe eu controlo as ações do usuário. Ela age como o controlador das aplicações testando qual ação o usuário escolheu e invocando o método correto (que deverá ser reescrito nas suas subclasses, as novas aplicações). A idéia é que cada aplicação seja uma subclasse da classe app.
Este é o código da classe app:

[Classe app](/codes/showphp.php?file=artigo_framework/codigos/classes/app.php)

Outra classe desenvolvida é a classe tabela. Esta classe é responsável por abstrair e facilitar a manipulação de tabelas de bancos de dados. Seu código fonte é descrito abaixo.

[Classe tabela ](/codes/showphp.php?file=artigo_framework/codigos/classes/tabela.php)

**Exemplo de aplicação**
Para ilustrar o funcionamento eu criei uma pequena aplicação com as classes. A aplicação é um sistema de blog, com posts e comentários.

A estrutura de diretórios ficou assim:
_classes/_ - diretório com as classes
_classes/app.php_  classe app
_classes/tabela.php_  classe tabela para tratamento de tabelas no banco de dados
_classes/adodb_ - classes adodb para abstração de bancos de dados. necessário para a classe tabela.
_classes/JSON.php_  para utilizar JSON, usado por algumas páginas que usam AJAX
_blog/_  diretório da aplicação
_blog/index.php_  subclasse da classe app
_blog/view/_ - diretório com as visões
_blog/view/index_view.php_  visão inicial
_blog/view/login_view.php_  visão da página de login
_blog/view/comentario_view.php_  visão dos comentários
_blog/view/admin_view.php_  visão da página de administração
_blog/view/estilo.css_  arquivo com as definições de CSS para as visões
_blog.sql_  arquivo sql com os comandos para criar as tabelas da aplicação
A primeira tarefa é criar a base de dados e as tabelas que serão utilizadas no exemplo. Para isso foram executados os seguintes comandos sql (gravados no arquivo blog.sql):

** **_create database blog;
use blog;
create table post (id_post int primary key auto_increment, tit_post varchar(255), ds_post text, dt_post date);
create table comentario(id_com int primary key auto_increment,ds_com text, email_com varchar(100), id_post int); _

** **A base de dados usada neste exemplo é o MySQL.
O código do arquivo index.php do diretório blog deve ser uma subclasse da classe app. O código inicial ficou desta forma:** **

[index.php - inicial](/codes/showphp.php?file=artigo_framework/codigos/partes/index_p1.php)

O primeiro método a ser escrito é o método index(). Este método é o método inicial da aplicação. O construtor da classe app sempre vai invocar este método caso não tenha sido escolhida outra opção.
Complementando o código:

[index.php - metodo index() ](/codes/showphp.php?file=artigo_framework/codigos/partes/index_p2.php)

O código do arquivo view/index_view.php é o seguinte:

[index_view.php](/codes/showphp.php?file=artigo_framework/codigos/blog/view/index_view.php)

O método showView da classe app vai transformar cada índice do vetor $dados em uma variável ou
em um novo vetor. Então o script index_view.php vai simplesmente imprimir seus valores.
Uma nova linha deve ser adicionada no final do arquivo index.php :

_$blog = new blog("mysql://root:@localhost/blog");_

Nesta linha é instanciado um novo objeto da classe blog criada. Como parâmetro para o construtor da classe é enviado a string de conexão com a base dados. Esta string é no formato usado pelo ADODB e a sintaxe para diversos bancos de dados podem ser encontradas no site da ferramenta.
Executando-se a aplicação deve-se obter o seguinte resultado:

[![img1.jpg](/wp-content/uploads/2006/05/img1.thumbnail.jpg)](/wp-content/uploads/2006/05/img1.jpg)
Todas as definições de cores, fontes e estilos foram adicionadas no arquivo estilo.css utilizando-se as técnicas de CSS. Desta forma, os arquivos de visão não possuem formatações de estilo e sim somente informações dos dados que devem ser gerados. A parte de formatação fica em separado, o que facilitaria caso fosse necessário alterar as definições de layout da aplicação. O código do arquivo estilo.css é o seguinte:

[estilo.css](/codes/showphp.php?file=artigo_framework/codigos/blog/view/estilo.css)

O próximo passo é criar as outras ações da aplicação. Por exemplo, quando o usuário clicar no link comentários ele será direcionado para:
http://localhost/blog/index.php?op=comentarios&id_post=15
Como a variável op controla a ação que o usuário escolheu precisamos definir um novo método na classe blog para atender a esta requisição. Então o seguinte código deve ser adicionado aos métodos da classe blog (arquivo index.php):

[index.php - método comentario()](/codes/showphp.php?file=artigo_framework/codigos/partes/index_p3.php)

O método comentarios() faz uso da visão comentario_view.php. Seu código é:

[comentario_view.php](/codes/showphp.php?file=artigo_framework/codigos/blog/view/comentario_view.php)

Assim, quando o usuário clicar no link Adicionar comentário na página inicial serão apresentados os comentários existentes e um formulário para adição de um novo comentário, conforme a imagem abaixo ilustra:

[![img2.jpg](/wp-content/uploads/2006/05/img2.thumbnail.jpg)](/wp-content/uploads/2006/05/img2.jpg)
Quando o usuário submeter os dados do novo comentário a ação addComentario será executada, como indicado pelo input hidden chamado op na linha 26 do comentario_view.php. Para que esta ação seja executada o seguinte código deve ser adicionado no arquivo index.php.

[index.php - método addComentario()](/codes/showphp.php?file=artigo_framework/codigos/partes/index_p4.php)

Quando o usuário clica no link Admin da página inicial ele é redirecionado para a visão que mostra o formulário de login. O método da classe blog que realiza esta ação é:

[index.php - método mostraLogin()](/codes/showphp.php?file=artigo_framework/codigos/partes/index_p5.php)

** **E o código fonte do arquivo login_view.php pode ser visualizado abaixo:

[login_view.php](/codes/showphp.php?file=artigo_framework/codigos/blog/view/login_view.php)

O método login da classe blog é responsável pela validação do usuário e por mostrar a visão de administração. Neste exemplo não é feito nenhuma validação específica, isso é deixado a cargo do leitor, podendo implementar algum método de autenticação que seja pertinente.

[index.php - método login()](/codes/showphp.php?file=artigo_framework/codigos/partes/index_p6.php)

A visão de administração é a mais complexa de todas. Além de mostrar os posts já cadastrados na tabela ela fornece opções de exclusão e de alteração dos mesmos. Para melhorar a interação com o usuário é usado técnicas de AJAX para buscar os dados do post antes do usuário realizar a alteração.
O código do arquivo admin_view.php é mostrado abaixo, com seus comentários.

[admin_view.php](/codes/showphp.php?file=artigo_framework/codigos/blog/view/admin_view.php)

Quando o usuário clica no link Alterar uma conexão assíncrona é aberta com o servidor via AJAX solicitando os dados do post. Os dados são codificados no formato JSON. JSON é um formato leve para troca de informações. É facil para humanos lerem e escreverem. E é fácil para as máquinas processar e gerar. Uma espécie de XML ligth. É baseado na notação de objetos do JavaScript, o que cai como uma luva para usar com o XMLHttpRequest. A classe blog utiliza o include JSON.php para gerar os dados neste formato. Este script pode ser encontrado no repositório PEAR, no endereço http://pear.php.net/pepr/pepr-proposal-show.php?id=198 e mais informações sobre o JSON podem ser encontrados no http://www.json.org.
Um exemplo da visão de administração é mostrado na figura abaixo:

** ****[![img3.jpg](/wp-content/uploads/2006/05/img3.thumbnail.jpg)](/wp-content/uploads/2006/05/img3.jpg)
** Abaixo é mostrado o código final da classe blog com todos os métodos comentados acima e os métodos restantes, addPost(), del(), altPost() e buscaPost().

[index.php - arquivo final](/codes/showphp.php?file=artigo_framework/codigos/blog/index.php)

Tenho usado estas classes em alguns novos projetos que estou desenvolvendo e o ganho de produtividade tem sido interessante. Se ajudar para alguém fica aí minha contribuição.
