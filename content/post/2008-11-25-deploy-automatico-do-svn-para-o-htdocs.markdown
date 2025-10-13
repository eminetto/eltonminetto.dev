---
categories:
- docs
- php
- python
comments: true
date: 2008-11-25T14:17:25Z
slug: deploy-automatico-do-svn-para-o-htdocs
tags:
- svn
title: None
url: /2008/11/25/deploy-automatico-do-svn-para-o-htdocs/
wordpress_id: 325
---

Estou iniciando um novo projeto e aproveitei para mudar do CVS para o Subversion. 

Uma das coisas que achei interessante é o esquema de "_hooks_". É um conceito parecido com "_triggers_" de bancos de dados. Você pode programar alguns scripts para serem executados em momentos específicos do ciclo gerenciado pelo SVN. As opções são:

    
    post-commit.tmpl



    
    post-lock.tmpl



    
    post-revprop-change.tmpl



    
    post-unlock.tmpl



    
    pre-commit.tmpl



    
    pre-lock.tmpl



    
    pre-revprop-change.tmpl



    
    pre-unlock.tmpl



    
    start-commit.tmpl


Os nomes são auto-explicativos. Por exemplo, o script post-lock vai ser executado sempre após algum usuário ter feito o lock de um arquivo.

Estes arquivos estão armazenados no diretório _hooks _do repositório do projeto.

O que eu fiz foi alterar o post-commit.tmpl

É preciso remover a extensão do nome e dar permissão de execução no arquivo, então:

    
    cp post-commit.tmpl post-commit



    
    chmod +x post-commit


O conteúdo do arquivo ficou assim:

    
    <span style="font-family:'Lucida Grande';line-height:19px;white-space:normal;"><span style="font-family:'Courier New';line-height:18px;white-space:pre;">REPOS="$1"</span></span>



    
    REV="$2"



    
    PROD="/var/www/html"



    
    #pega todas as alteracoes



    
    svnlook changed $REPOS --revision $REV >> /tmp/lixo_$REV



    
    #pega cada alteracao e salva



    
    for i in `cat /tmp/lixo_$REV|cut -c 5-1024` ; do



    
      svnlook cat $REPOS $i > $PROD/$i



    
    done



    
    #apagar arquivo



    
    rm /tmp/lixo_$REV




Desta forma cada vez que um programador faz o commit do fonte ele é automaticamente salvo no htdocs, onde fica acessível para a equipe de testes. 




Lógico que esse script pode ser melhorado e isso está sendo executado em um servidor de desenvolvimento e não o de produção. Além disso eu comecei a usar o SVN somente agora, por isso, se alguém encontrar um problema ou erro na lógica me avisem :-)
