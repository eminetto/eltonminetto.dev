---
categories:
- etc
comments: true
date: 2012-05-14T14:18:19Z
slug: usando-jekyll-e-github-pages
title: Usando Jekyll e Github Pages
url: /2012/05/14/usando-jekyll-e-github-pages/
wordpress_id: 985
---

A [Coderockr](http://coderockr.com) está de site novo!
Havíamos iniciado o projeto a algumas semanas mas a grande carga de projetos não nos permitia terminar a implementação da nossa idéia inicial: um backend desenvolvido usando [Slim Framework](/blog/2011/11/29/slim-framework/) e uma interface que leria os dados de projetos e demais textos de um banco de dados MySQL.
Como precisávamos renovar o site adicionando alguns projetos que fizemos nos últimos meses resolvi tentar outra abordagem. Eu estou estudando o [Octopress](http://octopress.org/) como substituto ao Wordpress para este site, e como ele é baseado no [Jekyll](http://jekyllrb.com/) fiz um teste usando o [Github Pages](http://pages.github.com/) para armazenar o nosso novo site. 
A parte mais complexa nós já tinhamos, que era o belo design feito pelo [@thiagovieiracom](http://twitter.com/thiagovieiracom), só faltava configurar o Github e o Jekyll. 
O primeiro passo foi instalar o Jekyll. No Mac eu precisei executar:

```
sudo gem install jekyll
```

Depois é preciso criar um novo repositório no Github para a sua página. No caso da Coderockr criamos

```
coderockr.github.com
```

É sempre necessário criar nesse formato: usuario.github.com. Outro exemplo, o teste que estou fazendo com o Octopress: [http://eminetto.github.com](http://eminetto.github.com)

Depois de configurar o repositório na minha máquina eu criei a seguinte estrutura de diretórios/arquivos:

```
coderockr.github.com
    _includes
        andre.html : currículo do @xorna
        destaque_1.html : conteúdo HTML do primeiro projeto em destaque da página
        destaque_2.html : conteúdo HTML do segundo projeto em destaque da página
        destaque_3.html : conteúdo HTML do terceiro projeto em destaque da página
        elton.html : meu currículo
        marcos.html : currículo do @msilvagarcia
        projects.html : template usado para apresentar os projetos da Coderockr
        thiago.html : currículo do @thiagovieiracom
    _layouts
        default.html : layout default das páginas. Nesse caso só mostra o conteúdo delas 
    _posts : vou explicar daqui a pouco
    _site: diretório gerado automaticamente pelo Jekyll com o html final do site
    public :  diretório com os arquivos JS, CSS e imagens, que será copiado para o _site
    _config.yml : arquivo de configurações do Jekyll
    CNAME : necessário para poder usar um domínio diferente de usuario.github.com, nesse caso coderockr.com
    index.html :  página html com tags do sistema de templates usado pelo Jekyll para carregar os posts e includes
```

Como o Jekyll foi criado para ser um sistema de blogs fizemos uma pequena adaptação no seu uso, considerando cada projeto da Coderockr como um post em um blog, salvando os arquivos na pasta _posts. É necessário que o arquivo seja salvo no formato _2012-05-10-arkpad.html_ (ano-mes-dia-url)
Os arquivos podem ser criados no formato HTML ou Markdown, bastando renomeá-los para .md e o Jekyll vai fazer o parse e gerar um HTML estático com o conteúdo final.

O Jekyll usa um sistema de templates chamado Liquid bem fácil de usar. Exemplo de trecho do index.html usado para gerar os currículos:

{% raw %}
<div class="wrap">
  <ul>
    <li>
      {% include elton.html %}
    </li>
    <li class="last">
      {% include andre.html %}
    </li>
    <li>
      {% include marcos.html %}
    </li>
    <li class="last">
      {% include thiago.html %}
    </li>
  </ul>
</div>
{% endraw %}

Também é possível usar loops para mostrar os projetos (posts):

{% raw %}
<li class="wrap">
    <ul>
      {% for post in site.posts limit:6 offset:0 %}
        {% include projects.html %}   
      {% endfor %}
    </ul>
</li>        
{% endraw %}
O conteúdo de cada post é simples, como por exemplo:

``` 
---
title: arkpad
type: iphone
image: /public/images/content/icon-arkpad.png
description: Tudo que existe de melhor no mercado de arquitetura, decoração e design está disponível no arkpad.
gallery:
    - /public/images/content/app-arkpad-1-4.jpg
    - /public/images/content/app-arkpad-2-4.jpg
    - /public/images/content/app-arkpad-3-4.jpg
    - /public/images/content/app-arkpad-4-4.jpg
---
Nele é possível navegar no rico catálogo de produtos (e seus fornecedores) com curadoria do serviço, consultar uma biblioteca visual de ambientes decorados, além de poder organizar suas próprias pastas de projetos com o conteúdo do aplicativo. Tudo sincronizado com o portal, http://arkpad.com.br.
<p class="app"><a href="http://itunes.apple.com/br/app/arkpad/id403784852?mt=8" title="App Store" target="_blank"><img src="/public/images/appstore.png" width="104" height="30" alt="App Store" /></a>
```

Nas primeiras linhas criamos variáveis (entre os três hífens) e um código html.
No arquivo projects.html podemos usar as variáveis post.title, post.type, e o post.content que é o conteúdo HTML do post.

Para que o site responda pelo domínio oficial (coderockr.com) é preciso criar o arquivo CNAME com o nome do domínio e modificar o DNS para o endereço IP do Github.

As vantagens de usar essa abordagem foram várias, como:
- facilidade de teste. Nas máquinas locais basta executar jekkyl --server --auto e acessar o endereço http://localhost:4000 para verificar o resultado gerado
- velocidade. As páginas agora são estáticas, não dependendo mais de bancos de dados, o que aumenta bastante a performance e facilita o uso de cache
- facilidade de alteração. Basta fazer um clone do repositório, alterar o necessário, fazer o commit e o push para que o Github execute o Jekyll e gere novamente as páginas estáticas
- hospedagem gratuíta. O Github não cobra extra pela hospedagem das páginas

É nosso primeiro teste com essa ferramenta, mas estou gostando bastante do resultado e pretendo usar em mais projetos da Coderockr e provavelmente como plataforma de blog para esse site.
