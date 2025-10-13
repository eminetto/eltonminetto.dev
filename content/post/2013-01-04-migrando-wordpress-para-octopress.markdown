---
categories:
- codes
- etc
comments: true
date: 2013-01-04T14:00:00Z
slug: migrando-wordpress-para-octopress
title: Migrando do Wordpress para o Octopress
url: /2013/01/04/migrando-wordpress-para-octopress/
---

Eu mantenho esse site desde meados de 2003 e nesse tempo venho usando o Wordpress como plataforma. Ele sempre foi uma boa opção, mas um detalhe vinha me deixando nervoso nos últimos tempos: eu passava mais tempo formatando os textos, imagens e códigos do que realmente escrevendo o texto. 

Recentemente descobri o [Markdown](http://daringfireball.net/projects/markdown/) para realizar as marcações e gostei muito, tanto que [usamos no novo site](http://eltonminetto.net/blog/2012/05/14/usando-jekyll-e-github-pages/) da [Coderockr](http://coderockr.com) e eu [usei para escrever](http://eltonminetto.net/blog/2012/11/29/escrevendo-um-livro-do-modo-nerd/) o e-book [Zend Framework 2 na prática](http://www.zfnapratica.com.br).

Na construção do site da Coderockr nós usamos a dupla Jekyll e Github Pages. Nesse site eu usei uma solução um pouco diferente: Octopress, Google Analytics, Disqus e Amazon S3.

Nesse post vou comentar sobre o processo de migração e as ferramentas usadas

<!--more-->

## Octopress

A primeira ferramenta escolhida foi o [Octopress](http://octopress.org). Segundo o site (tradução livre):

"Octopress é um framework desenvolvido por Brandon Mathis para o Jekyll, o gerador de páginas estáticas usado pelo Github Pages. Para iniciar um blog com o Jekyll é necessário escrever seu próprio template HTML, CSS, Javascripts e configurações. Mas com o Octopress tudo isso já está pronto. Basta clonar ou fazer um fork do Octopress, instalar dependências, plugins e temas e começar a usar"
	
A [documentação](http://octopress.org/docs/) auxilia a instalação e configurações básicas.

Depois de tudo instalado você pode usar o tema padrão ou escolher algum disponível no Github. Nesse [site](https://github.com/imathis/octopress/wiki/3rd-Party-Octopress-Themes) é possível encontrar alguns. Eu estou usando o [Foxslide](https://github.com/sevenadrian/foxslide) que já vem com alguns widgets úteis como Twitter, Instagram e Github. Você também pode [criar um novo tema](http://octopress.org/docs/theme/) caso tenha os skills necessários.

Agora você pode criar novos posts ou usar alguma ferramenta de migração, como eu fiz.

## Migrando os posts do Wordpress

Eu usei a ferramenta [exitwp](https://github.com/thomasf/exitwp) para fazer a migração dos posts do Wordpress para o formato Markdown que o Jekyll usa. Para isso basta seguir as instruções que estão no Github. Um detalhe importante é o seguir a recomendação de incluir o 
	
	xmlns:atom="http://www.w3.org/2005/Atom"

no arquivo XML gerado pelo Wordpress. Sem isso você vai ter diversos problemas na importação.

Após executado o exitwp ele vai gerar um diretório chamado build com os arquivos em formato Markdown. Você precisa salvá-los no diretório *source/_posts* da sua instalação do Octopress.

## Testando

Depois de ter escrito algum post, ou importado do Wordpress, você pode testar o resultado executando no terminal os comandos:

	rake generate
	rake preview

O primeiro comando processa todos os arquivos e gera o diretório *public* com os arquivos estáticos que você vai fazer deploy para o servidor mais tarde. Com o segundo comando um servidor local é executado e você pode testar no seu navegador com a url *http://localhost:4000*

## Comentários, share buttons e Analytics


Uma das vantagens do Wordpress é ele ser uma plataforma bem completa e madura para blogs, já possuindo ferramentas como comentários, botões de compartilhamento e estatísticas. Com o Octopress eu estou usando algumas ferramentas separadas:

1. Estou usando o [Disqus](http://disqus.com) para armazenar os comentários. Funciona muito bem e dá a praticidade dos visitantes comentarem usando seus usuários do Twitter e Facebook. Possui moderação e controle de spam (via Akismet, a mesma ferramenta usada pelo Wordpress). O sistema de importação de comentários do Wordpress é meio problemática, mas a ferramenta é boa. 

2. Para os botões de compartilhamneto estou usando o [ShareThis](http://sharethis.com). Basta cadastrar-se, customizar a aparência dos botões e colar o código no template dos artigos do Octopress. 

3. E para as estatísticas estou usando o bom e velho [Google Analytics](http://google.com/analytics) que dispensa apresentações.


## Deploy


Como os arquivos gerados são apenas arquivos HTML você pode fazer o deploy para qualquer servidor web como o Apache, Nginx, etc. No meu caso eu optei por outra opção um pouco menos ortodoxa, a dupla Amazon S3 e Amazon Route 53.

O S3 é um repositório de arquivos, mas recentemente eles adicionaram uma opção bem interessante, a possibilidade de servirem arquivos estáticos sem a necessidade de configurar um servidor para isso. Basta criar um *bucket* (nome dado pelo S3 aos diretórios) e habilitar a opção "Static Website Hosting". Também é possível fazer redirecionamentos entre *buckets*, por isso eu criei um chamado *www.eltonminetto.net* vazio que redireciona para o *eltonminetto.net*. O Route 53 é o serviço de DNS da Amazon e estou usando ele para configurar o domínio. A Amazon faz a cobrança de acordo com o espaço armazenado e com a quantia de arquivos transferidos, e o fato de não precisar ter um servidor configurado deve reduzir mais ainda os custos. Além disso se você criar uma conta nova tem um ano de desconto em vários serviços.

O último passo que eu configurei foi a opção de deploy do próprio Octopress. Eu segui essa [documentação](http://www.snikt.net/blog/2012/11/03/moving-octopress-to-amazon-s3-and-cloudfront/) e depois de tudo configurado basta executar o comando:

	rake deploy
	
e os arquivos são enviados diretamente para o S3.

## Vantagens

Algumas vantagens que percebi até agora:

1. Poder escrever em Markdown, sem me preocupar com formatações de códigos, espaços, etc. Eu uso o editor [Mou](http://mouapp.com/) no Mac para escrever o Markdown e acompanhar o resultado da formatação em tempo real. 

2. Não precisar me preocupar em atualizar a plataforma de blogs e o servidor

3. Usar um repositório git para armazenar os textos, facilitando o backup e versionamento. Hoje estou usando um repositório privado no [Bitbucket](http://bitbucket.org) para isso.

## Desvantagens

Como o [Alex Piaz](https://twitter.com/zaip) comentou no Facebook, o Jekyll/Octopress é "coisa de dev pra dev". Realmente para o usuário final é muito mais fácil usar o painel do Wordpress para escrever e formatar os textos. Mas para desenvolvedores é muito mais flexível.

Outra desvantagem é que preciso ter o ambiente configurado (Ruby, Jekyll) para postar algo ou corrigir algum erro. Com o Wordpress era necessário somente um navegador ou o iPad. Isso pode ser resolvido com alguma dose de programação como um shell script na _crontab_ que faça o _git pull_ e o _rake deploy_ de tempos em tempos (mas para isso eu precisaria incluir um servidor na solução). 

Acho que o painel do Wordpress também vai me fazer alguma falta. Com ele eu podia acompanhar os comentários e estatísticas de um só local e agora vou precisar entrar em mais de uma ferramenta para isso.

## Conclusão

Vou usar essa solução por algum tempo e comentando as novas vantagens e desvantagens que for descobrindo no processo. Por enquanto estou gostando bastante da aventura.
