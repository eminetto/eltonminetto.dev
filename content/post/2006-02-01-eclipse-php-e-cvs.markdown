---
categories:
- home
- ubuntu
comments: true
date: 2006-02-01T16:11:21Z
slug: eclipse-php-e-cvs
title: Eclipse, PHP e CVS
url: /2006/02/01/eclipse-php-e-cvs/
wordpress_id: 99
---

## Eclipse


O [Eclipse](http://www.eclipse.org) é um projeto open source iniciado pela IBM e que hoje é uma das melhores IDEs do mercado. O Eclipse foi desenvolvido inicialmente como uma IDE para Java, mas graças a seu sistema de plugins hoje é possível usá-lo para programar em quase todas as linguagens existentes, dentre elas PHP.
O plugin para trabalhar com PHP no Eclipse é o [PHPeclipse](http://www.phpeclipse.de/) . Sua instalação é simples, bastando descompactar o arquivo no mesmo diretório onde está instalado o Eclipse.
Ele fornece várias facilidades para o programador PHP, como opção de iniciar e parar o apache e o mysql, autocompletar de comandos, highlighting , lista de funções/métodos do arquivo, projetos, etc, como mostra a figura abaixo

[![PHPeclipse](/wp-content/uploads/2006/02/figura0.thumbnail.png)](/wp-content/uploads/2006/02/figura0.png)


## CVS


O CVS é um dos mais usados sistemas de controle de versões. Apesar de existirem outras opções como o Subversion, ele ainda atende as necessidades de diversos projetos. No decorrer deste texto mostrarei como criar um repositório e como usar o Eclipse para gerenciar os arquivos de um projeto.
Para este exemplo foi utilizado um computador com o Ubuntu 5.10 para servir como repositório central do projeto. Os clientes, no caso o Eclipse rodando no Linux ou no Windows, irão usar o servidor para salvar os arquivos do projeto. A instação do CVS é simples, bastando executar

_sudo apt-get install cvs_

para que o pacote seja instalado.

**Iniciar o projeto no CVS **

Exportar a variável CVSROOT

_export CVSROOT=/home/cvs_

Criar o diretório onde vão ser armazenados os arquivos dos projetos
_mkdir /home/cvs_

O melhor é criar um grupo cvs, adicionar os usuários nesse grupo e dar as permissões corretas para o diretório

_chown -R cvs /home/cvs
chmod g+rwx /home/cvs
_
Com o comando abaixo é inicializado o repositório
_cvs init_
Para importar os arquivos de um projeto antigo para o repositório, os comandos são:

_cd path_projeto_antigo
cvs import -m "mensagem" nome_projeto_repositorio nome_release nome_tag_

_
Exemplo:
__cd /home/elm/Projects/saa
cvs import -m "Sistema de Auto Atendimento" saa R1 inicio
_
Os arquivos serão copiados para o diretório /home/cvs, indicado pela variável CVSROOT

_ __Depois disto pode-se compactar e remover os arquivos do diretório /home/elm/Projects/saa para não correr o risco de usar os programas que não estão no repositório_

_ __**Utilizando o Eclipse para acessar o repositório**_

_ __No Eclipse, pode-se criar um novo projeto do tipo PHP e importar os arquivos do CVS para ele._

_ __[![figura1.png](/wp-content/uploads/2006/02/figura1.thumbnail.png)](/wp-content/uploads/2006/02/figura1.png)_

_ __[![figura2.png](/wp-content/uploads/2006/02/figura2.thumbnail.png)](/wp-content/uploads/2006/02/figura2.png)_

_ __Após criado o projeto, pode-se usar a opção de importar os arquivos para o projeto. Clicando-se com o segundo botão sobre o projeto criado, opção Import_

_ __[![figura3.png](/wp-content/uploads/2006/02/figura3.thumbnail.png)](/wp-content/uploads/2006/02/figura3.png)_

_ __[![figura4.png](/wp-content/uploads/2006/02/figura4.thumbnail.png)](/wp-content/uploads/2006/02/figura4.png)_

_ __Na figura abaixo são demonstrados as informações necessárias para conectar no servidor._

_ __[![figura51.png](/wp-content/uploads/2006/02/figura51.thumbnail.png)](/wp-content/uploads/2006/02/figura51.png)
__ Host:_ endereço ip ou nome do servidor onde está o repositório
_ Repository path:_ o mesmo valor do CVSROOT, no exemplo, /home/cvs
_ User:_ cvs ou qualquer usuário que pertença ao grupo cvs, pois os membros deste grupo tem acesso a gravar os dados no diretório do repositório
_ Connection type:_extssh
_ Use default port:_ A conexão será feita pelo protocolo SSH, então o servidor deste protocolo deve estar sendo executado no servidor. Caso o servidor sshd esteja sendo executado em outra porta que não a padrão, pode-se informá-la na opção Use port.
_ Save password:_ pode ser marcado para que não seja preciso digitar a senha do usuário a cada operação. A senha será gravada localmente, por isso, paranóicos por segurança podem preferir não marcar esta opção

_ __[![figura6.png](/wp-content/uploads/2006/02/figura6.thumbnail.png)](/wp-content/uploads/2006/02/figura6.png)_

_ __[![figura7.png](/wp-content/uploads/2006/02/figura7.thumbnail.png)](/wp-content/uploads/2006/02/figura7.png)_

_ __[![figura8.png](/wp-content/uploads/2006/02/figura8.thumbnail.png)](/wp-content/uploads/2006/02/figura8.png)_

_ __No detalhe da figura abaixo é possível ver o nome do arquivo e a versão atual (1.1.1.1)_

_ __[![figura9.png](/wp-content/uploads/2006/02/figura9.thumbnail.png)](/wp-content/uploads/2006/02/figura9.png)_

_ __**Salvando os arquivos no CVS**_

_ __Após alterar os arquivos localmente é preciso salvar as alterações no CVS. Para fazer isso é só clicar com o segundo botão sobre o arquivo alterado (note que aparece um sinal > no início do arquivo mostrando que ele sofreu alterações desde a última sincronização) e escolher a opção Team -> Commit. Uma tela é mostrada pedindo que seja informado um comentário sobre o que foi alterado nessa nova versão, como mostra a figura abaixo._

_ __[![figura10.png](/wp-content/uploads/2006/02/figura10.thumbnail.png)](/wp-content/uploads/2006/02/figura10.png)_

_ __Na figura abaixo é possível ver que o arquivo agora está na versão 1.2_

_ __[![figura11.png](/wp-content/uploads/2006/02/figura11.thumbnail.png)](/wp-content/uploads/2006/02/figura11.png)
**Comparando as alterações**_

_ __Clicando com o segundo botão no arquivo, opção Compare With ->Revision é possivel ver todas as versões existentes do programa. Clicando duplo em uma das versões anteriores é mostrado uma tela com a versão atual e mostrando o que foi alterado entre a versão atual e a antiga.Como mostra a figura abaixo._

_ __[![figura12.png](/wp-content/uploads/2006/02/figura12.thumbnail.png)](/wp-content/uploads/2006/02/figura12.png)
Outra vantagem de se utilizar um sistema de controle de versões como o CVS é o trabalho colaborativo. Se outra pessoal alterou o arquivo, no momento do commit é mostrado as suas alterações e as da outra pessoa. Assim pode-se decidir qual das duas, ou as duas, devem ficar no arquivo e deve-se executar o commit novamente._

_ __Este é apenas um resumo das principais funções do CVS. Mais informações podem ser encontradas no manual do CVS ou na excelente [Apostila de CVS](http://www.rau-tu.unicamp.br/nou-rau/softwarelivre/document/?code=80&tid=3)  escrita por André Duarte Bueno, que serviu de fonte para este texto._
