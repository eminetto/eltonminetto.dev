---
categories:
- home
comments: true
date: 2006-07-20T22:18:57Z
slug: seguranca
title: None
url: /2006/07/20/seguranca/
wordpress_id: 150
---

É incrível como por mais que façamos testes e temos a segurança em mente no desenvolvimento de sistemas sempre alguma coisa "escapa". O amigo William percebeu um problema em um script que estou usando aqui no meu site para demonstrar os códigos php de uma maneira mais organizada.

No script era passado o nome do arquivo para ser formatado. No início do script tomei o cuidado de verificar se somente arquivos de determinados formatos fossem visualizados. Para isso usei o seguinte código:

$file = $_GET[file];
$extensao = strtolower(end(explode('.', $file)));
if($extensao != 'php' && $extensao != 'html' && $extensao != 'htm' && $extensao != 'css' && $extensao != 'js') {
echo "Somente arquivos php";
exit;
}

Assim somente códigos php, html, htm, css e js podem ser visualizados. Mas mesmo assim estes arquivos podem conter informações importantes como senhas de bancos de dados, nomes de usuários, etc. Assim, este código não estava impedindo que fosse passado como parâmetro o caminho completo de um arquivo como por exemplo:

showphp.php?file=../wp-config.php

Este arquivo contém todas as configurações do wordpress, inclusive nome de usuário e senha da base de dados. Para resolver esse problema usei uma configuração do servidor Apache onde está hospedado meu site. Coloquei o trecho abaixo no arquivo httpd.conf:


php_admin_value open_basedir /home/elm/public_html/codes

Desta forma se alguém tentar usar o showcodes.php para tentar visualizar arquivos em outras pastas receberá um erro dizendo que o script não tem permissão de acessar outros diretórios. É o que faz a função open_basedir, dizendo que todos os scripts existentes dentro do diretório codes só podem acessar arquivos  no diretório codes. No arquivo php.ini também é possivel configurar esta variável para restringir o acesso mas fazendo desta forma todos os scripts do servidor são atingidos. Usando da forma que eu fiz, no apache e indicando o diretório eu não altero o comportamento de scripts de outros usuários do servidor.
