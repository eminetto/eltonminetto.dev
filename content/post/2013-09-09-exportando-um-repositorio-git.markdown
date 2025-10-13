---
categories:
- git
comments: true
date: 2013-09-09T00:00:00Z
title: Exportando um repositório Git
url: /2013/09/09/exportando-um-repositorio-git/
---

Mais um daqueles momentos que me deixam feliz por trabalhar na área de tecnologia: acabei de aprender um novo truque! Desta vez foi com o Git.

Eu precisava enviar para um cliente o diretório de códigos de um projeto e o arquivo _.zip_ gerado ficava com 200 MB. Analisando o projeto encontrei o problema: a pasta _.git_ tinha mais de 100 MB, devido ao histórico de alterações e revisões. O que eu precisava era exportar apenas os arquivos, sem o histórico do repositório. Para quem usa SVN existe o comando _svn export_,mas e para projetos Git?

Com uma pequena pesquisa no Google encontrei a solução [nesse post](http://stackoverflow.com/questions/160608/how-to-do-a-git-export-like-svn-export). A solução que usei foi:

```php
cd Projeto
git archive --format zip --output /tmp/Projeto.zip master
```
E pronto! O arquivo _.zip_ gerado ficou com menos de 100MB, que é somente o tamanho dos arquivos do projeto (códigos e imagens de design). 

Fica aqui mais essa dica que pode ser útil para alguém.
