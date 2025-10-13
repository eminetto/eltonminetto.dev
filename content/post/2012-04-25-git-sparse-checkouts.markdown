---
categories:
- coderockr
comments: true
date: 2012-04-25T13:45:14Z
slug: git-sparse-checkouts
title: Git sparse checkouts
url: /2012/04/25/git-sparse-checkouts/
wordpress_id: 966
---

Ontem eu e o colega [Marcos Garcia](http://twitter.com/msilvagarcia) nos deparamos com uma situação que acabou nos ensinando mais um truque do git
Um dos repositórios privados da [Coderockr](http://www.coderockr.com) no Github tem uma estrutura parecida com essa:

```
Design - arquivos PSD e PNG com as telas do projeto
Docs - documentações do projeto
Android - porção Android 
iOS - porção de códigos para o iPhone/iPad
Web - interface de administração do projeto, acessível via web
library - diretório com entidades e webservices usado pelo projeto
```

A nossa necessidade era de fazer o deploy para o nosso servidor web apenas da pasta Web do projeto, não necessitando das demais. O git permite fazer algo assim usando [submodulos](http://help.github.com/submodules/) mas não era exatamente o que procurávamos, principalmente porque cada submodulo deve ser um repositório separado no Github, o que iria aumentar os nossos custos mensais.
Depois de algumas pesquisas no Google o Marcos encontrou a solução pro problema: o git sparse checkouts, uma opção que foi lançada com a versão 1.7 do git. 
Para exemplificar o uso, no nosso servidor agora fazemos o seguinte:

```
git clone <repository_url> <directory>
cd <directory>
```

Habilitamos a opção do sparse checkout

```
git config core.sparsecheckout true
```

Indicamos quais diretórios queremos ter acesso:

```
echo Web >> .git/info/sparse-checkout
echo library >> .git/info/sparse-checkout
```

E rodamos o comando _read-tree_ para atualizar a árvore de diretórios 

```
git read-tree -m -u HEAD
```

Se verificar com o comando _ls_ é possível ver que os outros diretórios desapareceram do diretório do projeto
Quando rodarmos um git pull para atualizar, apenas os diretórios Web e library serão atualizados.
Com isso conseguimos resolver o nosso problema sem aumentar os nossos custos ;)
