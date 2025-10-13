---
categories:
- home
- python
- ubuntu
comments: true
date: 2007-03-24T15:55:14Z
slug: komodo-edit
title: Komodo Edit
url: /2007/03/24/komodo-edit/
wordpress_id: 198
---

Estou testando um novo editor para programação que estou achando interessante. É o [Komodo Edit](http://www.activestate.com/products/komodo_edit/), da empresa ActiveState. Ele é a versão gratuíta da ferramenta [Komodo IDE](http://www.activestate.com/products/komodo_ide/) que é bem mais completa. Ele possui versões para Windows, MacOS e Linux. No Ubuntu deve-se fazer o download do arquivo

Komodo-Edit-4.0.2-275451-linux-libcpp6-x86.tar.gz

que encontra-se no site da ferramenta. A instalação resume-se a executar o script install.sh e indicar onde será instalado.
Apesar de possuir menos recursos do que a versão paga ela possui várias características que um bom editor precisa, como syntax highlighting, code folding, etc. Ela possui suporte para PHP, Python (inclusive Django), Perl, Ruby e JavaScript. É uma ferramenta muito legal para trabalhar com desenvolvimento para Web. Só senti falta do suporte a versionamento de código (CVS e Subversion) que tem na versão paga. Além disso ela é bem mais leve que o Eclipse. Na verdade estou pensando em usar os comandos do CVS em um terminal e usar o Komodo para editar os códigos, consumindo bem menos memória do que o Eclipse (que possui suporte integrado ao CVS).

Uma característica legal é o suporte aos snippets, que são trechos de códigos que podem ser automatizados. Criei alguns snippets para o PHP. Para instalar é só fazer o download deste [arquivo](/codes/php_snippets.kpz) e salvar em seu computador. Depois é preciso clicar em Views->Tabs e selecionar o Toolbox. Vai aparecer um novo painel na direita do editor. Lá deve-se clicar no ícone em forma de árvore de um navegador de arquivos e criar um novo Folder. Com o segundo botão sobre o novo folder escolhe-se Import Package e indica o arquivo que foi copiado. Assim os snippets estão instalados e podem ser usados com um duplo-clique sobre ele ou com a tecla Enter. De dentro de um código sendo editado dá para usar as teclas ALT+B, selecionar o snippet e teclar Enter. Assim nem do mouse precisa :-)

Se alguém quiser melhorar os snippets ou criar os de outra linguagem coloquem nos comentários o link. Seria útil
