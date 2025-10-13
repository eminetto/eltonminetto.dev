---
categories:
- codes
- home
- python
comments: true
date: 2010-03-10T11:34:05Z
slug: contabilizar-espaco-usado-em-ftp-usando-python
title: Contabilizar espaço usado em FTP usando Python
url: /2010/03/10/contabilizar-espaco-usado-em-ftp-usando-python/
wordpress_id: 412
---

Aqui na [empresa](http://www.drimio.com) temos duas contas de FTP contratadas no nosso plano de hospedagem para usarmos como espaço de backup. As duas contas tem um espaço limitado e toda semana eu preciso monitorar quanto espaço estamos usando, para evitar erros no script de backup. Para facilitar esse controle eu criei um pequeno script Python para navegar em todas as pastas e me dizer quanto espaço estou usando. O script ficou assim:

    
    
    import os
    from ftplib import FTP
    
    def pretty_filesize(bytes):
    	if bytes >= 1073741824:
    		return str(bytes / 1024 / 1024 / 1024) + ' GB'
    	elif bytes >= 1048576:
    		return str(bytes / 1024 / 1024) + ' MB'
    	elif bytes >= 1024:
    		return str(bytes / 1024) + ' KB'
    	elif bytes < 1024:
    		return str(bytes) + ' bytes'
    
    endereco_ftp = 'servidor'
    usuario = 'usuario'
    senha = 'senha'
    conexao_ftp = FTP(endereco_ftp)
    
    conexao_ftp.login(usuario,senha)
    diretorio_corrente = conexao_ftp.pwd()
    soma = 0
    for i in conexao_ftp.nlst():
    	conexao_ftp.cwd(i)
    	for j in conexao_ftp.nlst():
    		soma = soma + conexao_ftp.size(j)
    	conexao_ftp.cwd('..')
    
    print pretty_filesize(soma)
    


Agora é só colocar no crontab e receber diariamente o espaço utilizado.
Fontes:
[http://www.vivaolinux.com.br/script/navegador-ftp](http://www.vivaolinux.com.br/script/navegador-ftp)
[http://code.rivers.pro/python-function-to-convert-bytes-to-kbmbgb/](http://code.rivers.pro/python-function-to-convert-bytes-to-kbmbgb/)
