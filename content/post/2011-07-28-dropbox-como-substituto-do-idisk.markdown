---
categories:
- etc
comments: true
date: 2011-07-28T18:02:07Z
slug: dropbox-como-substituto-do-idisk
title: Dropbox como substituto do iDisk
url: /2011/07/28/dropbox-como-substituto-do-idisk/
wordpress_id: 783
---

Com o lançamento do iCloud a Apple já avisou que em alguns meses vai descontinuar um dos serviços do MobileMe que eu mais usava, o iDisk. Então fiz uma pesquisa para achar um substituto e o escolhido foi o já amado por todos, o Dropbox. Mas tive dois pequenos problemas para resolver: como eu já uso o Dropbox para armazenar os dados da Coderockr precisava ter duas contas ativas no meu Mac. E o segundo problema é que o iDisk fornece uma pasta pública para acesso via web, útil para compartilhar coisas temporárias e o Dropbox não permite isso.
Para resolver o primeiro problema eu segui os passos [deste site](http://wiki.dropbox.com/TipsAndTricks/MultipleInstancesOnUnix)
Para solucionar o segundo problema eu configurei o Dropbox para Linux em um servidor que eu controlo, uma instância das mais simples no EC2 da Amazon. Eu segui os passos [deste site](http://wiki.dropbox.com/TipsAndTricks/TextBasedLinuxInstall), mas resumindo:
`
wget -O dropbox.tar.gz "http://www.dropbox.com/download/?plat=lnx.x86"
tar -xvzf dropbox.tar.gz
~/.dropbox-dist/dropboxd &
`
A primeira vez que você executar ele vai mostrar uma mensagem com um link para você acessar no navegador. Ele vai fazer com que a máquina Linux seja vinculada a sua conta do Dropbox. Após isso será criado um diretório Dropbox no seu home. 
Agora basta configurar o seu Apache para ter acesso a este diretório. No meu caso eu criei um domínio virtual:
`

        DocumentRoot "/home/eminetto/Dropbox/Public"
        ServerName public.eminetto.me


        
                Options Indexes MultiViews FollowSymLinks
                AllowOverride All
                Order allow,deny
                Allow from all
        

`
Lembre-se de dar permissão de leitura nos diretórios para o usuário do Apache ter acesso:
`
chmod 755 /home/eminetto
chmod 755 /home/eminetto/Dropbox
chmod 755 /home/eminetto/Dropbox/Public
`
Outra opção é usar algo como http://seuserver.com.br/~eminetto/Dropbox/Public, se o Apache tiver essa opção configurada.
Se o seu Apache estiver configurado para listar os arquivos do diretório você já pode ver os seus arquivos ao acessar via web. Caso não esteja, é possível usar um [script python](http://code.google.com/p/kosciak-misc/wiki/DropboxIndex) que lê o diretório e cria um index.html com o conteúdo. Depois de instalado você pode executar o comando:
` /usr/bin/dropbox-index -R /home/eminetto/Dropbox/Public/ `
Se tudo der certo, sempre que você alterar/adicionar arquivos no Dropbox do seu desktop (no meu Mac no caso, mas o Windows e o Linux deve funcionar na mesma forma) é sincronizado com o linux e estão visíveis na internet. Lembre-se de que o comando 
`~/.dropbox-dist/dropboxd & `
Deve estar sempre executando no Linux, pois é o responsável por fazer a sincronização entre a pasta local e o Dropbox.
Com algumas configurações adicionais no Apache você pode colocar autenticação e coisas mais complexas para incrementar a solução.
E que venha o iCloud. Agora o iDisk não vai me fazer mais falta
