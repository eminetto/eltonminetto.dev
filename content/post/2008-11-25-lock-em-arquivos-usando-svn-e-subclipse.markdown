---
categories:
- cakephp
- codes
- docs
- php
- python
comments: true
date: 2008-11-25T15:09:44Z
slug: lock-em-arquivos-usando-svn-e-subclipse
tags:
- svn
title: Lock em arquivos usando SVN e Subclipse
url: /2008/11/25/lock-em-arquivos-usando-svn-e-subclipse/
wordpress_id: 329
---

Outra novidade para mim ao usar o Subversion foi o controle de Locks. 

Eu sempre usei o CVS integrado ao Eclipse para gerenciar os projetos que eu trabalhava e com essa duplinha é bem fácil configurar para evitar que dois programadores alterem o mesmo arquivo.

Com o Subversion e o Eclipse (usando o plugin Subclipse) eu não encontrei essa opção. A solução que encontrei foi configurar o cliente do subversion para quando criar novos arquivos marcá-los com um flag. Este flag indica que, para editar o arquivo é preciso que seja feito o "lock" antes. No momento de criar o lock o Subclipse também verifica a versão do arquivo e avisa caso a versão local seja inferior a que consta no repositório. Desta forma eu garanto que o programador sempre tenha a última versão do arquivo e evito que duas pessoas alterem o mesmo arquivo ao mesmo tempo. Existem formas de corrigir isso usando práticas de merge, mas eu acho mais fácil evitar o problema do que resolvê-lo :-)

O que eu fiz foi alterar o arquivo config no diretório do usuário:

    
    mate ~/.subversion/config


Eu estou usando o Textmate no MacOSX. Mas o mesmo passo vai funcionar no Linux. No Windows XP o arquivo encontra-se no diretório

    
    c:\Documents and Settings\usuario\Dados de Aplicativos\Subversion\config


Neste arquivo eu alterei 

    
    # enable-auto-props = yes


para

    
    enable-auto-props = yes


E adicionei alinha abaixo na seção  [auto-props]

    
    * = svn:needs-lock


Desta forma, assim que o programador criar um novo arquivo e realizar o primeiro commit é adicionada esta flag ao arquivo. Todos que forem alterá-lo vão passar pela fase "lock-edit-commit", com um "update" caso seja necessário.
