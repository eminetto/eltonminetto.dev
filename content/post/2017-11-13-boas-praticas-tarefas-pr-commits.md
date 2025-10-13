+++
title = "Boas práticas na criação de milestones, tarefas, pull requests e commits"
subtitle = ""
date = "2017-11-13T10:54:24+02:00"
bigimg = ""
+++

Um dos fatores de sucesso do processo de desenvolvimento de software, assim como a maioria dos empreendimentos humanos, é uma boa comunicação. 

<!--more-->

A Gitlab, ao cunhar o termo "Conversational Development" foi muito feliz ao definir:
	
> ConvDev takes a different approach by constraining the agile principles to what’s at the center of getting work done, the conversation. 

Neste post vou citar algumas práticas que estamos implementando e que vem melhorando nossos processos. 

# Milestones/Épicos

O sucesso começa com a correta definição dos objetivos a serem alcançados. Um milestone, ou épico, dependendo da ferramenta usada pelo time, é uma forma de agruparmos uma série de tarefas e serve para termos uma visão geral do que estamos entregando para nossos usuários/clientes.

Usando uma definição [mais formal](https://www.entry.com/proper-use-of-project-milestones-in-the-field-of-project-management/) (traduzida e adaptada):

>  Um milestone é um evento significativo em um projeto que ocorre em um determinado momento. [...] usa entregáveis para identificar grandes segmentos de trabalho e datas finais. Milestones são pontos de controle em um projeto e deve ser fácil para todos reconhecê-los.

Por exemplo, se estamos desenvolvendo um novo site, três milestones poderiam ser:

- Criar o novo design
- Migrar o conteúdo para o novo design
- Publicar o novo site

Além do nome o milestone deve ter uma boa descrição definindo o impacto que esta entrega trará ao projeto. 


# Tarefas

Tendo em mente a "big picture" o próximo passo é quebrarmos esta entrega em pequenas tarefas. Para facilitar o processo nós criamos um template que nos ajuda a responder perguntas importantes como: o que é esperado? é um bug? é uma nova funcionalidade? é urgente?

Na Coderockr estamos usando o Github Issues como repositório de tarefas, então fizemos proveito de uma funcionalidade útil do Github, [os templates](https://github.com/blog/2111-issue-and-pull-request-templates). Em cada projeto criamos um arquivo chamado *ISSUE_TEMPLATE.md* que é automaticamente usado pela interface do Github no momento da criação da tarefa. Atualmente o conteúdo deste arquivo é:

[https://gist.github.com/eminetto/a2ad115fd08595c4f4252c1f8e7f1468](https://gist.githubusercontent.com/eminetto/a2ad115fd08595c4f4252c1f8e7f1468/raw/0132629d50c8c83e982c8a1a0ede63b389ef5dd0/ISSUE_TEMPLATE.md)

O resultado da sua utilização pode ser vista na imagem abaixo:

[![github-issue](/images/posts/github-issue.png)](/images/posts/github-issue.png) 

Nem todas as informações são necessárias em todas as tarefas. Por exemplo, alguns detalhes fazem mais sentido para bugs do que para melhorias. Mas ter este guia tem nos ajudado muito a pensar nos detalhes da necessidade, além de favorecer a criação de tarefas menores. 

Uma observação. Apesar do inglês ser a língua default (rá!) da área de desenvolvimento, não vejo problemas em adotar o nosso idioma no momento de documentarmos os processos. Acho que o mais importante é a equipe/projeto chegar a uma decisão de qual idioma usar e seguir um padrão. O importante é a comunicação.

Outro ponto que pode ser observado na imagem é o uso das labels do Github. Usamos elas para definir a categoria da tarefa, seu tipo, prioridade, complexidade, etc. O Lucas Abreu [escreveu um post](https://blog.coderockr.com/simplificando-o-setup-de-projetos-no-github-f29b76c83194) sobre como estamos usando as labels, além de um script para facilitar a criação delas em novos projetos. 

Na Code:Nation estamos testando o Pipefy como ferramenta de gerenciamento de projetos e ele permite customizarmos as tarefas para atender este mesmo padrão:

[![pipefy](/images/posts/pipefy.png)](/images/posts/pipefy.png) 

# Pull requests/merge requests

No nosso dia a dia usamos muito o conceito de "pair review", por isso todas as tarefas obrigatoriamente passam pelo processo de criação de um pull request e aprovação de outras pessoas. 

No Github basta criar um arquivo chamado *PULL_REQUEST_TEMPLATE.md* que será usado como template. Atualmente nosso arquivo é 

[https://gist.github.com/eminetto/b58a0fdd8037e38972e80539520043ba](https://gist.githubusercontent.com/eminetto/b58a0fdd8037e38972e80539520043ba/raw/5d0f7946e4dfb01ee0af34d91229660570ab86df/PULL_REQUEST_TEMPLATE.md)

mas ele pode mudar um pouco, dependendo do projeto.

Uma dica é usar as tags de comando do Github (que também funcionam no Bitbucket e Gitlab) como "closes", "connected to", etc. Assim a interface da ferramenta consegue fazer ligações entre tarefas e pull requests, ou mesmo finalizar uma tarefa no momento que o pull request é aceito. 

Nós padronizamos a criação do nome da branch usando o mesmo id sequencial da tarefa. Então a tarefa 171 gera uma branch chamada *issue-171*, que é usada no pull request. Algumas equipes acham isso pouco produtivo e preferem um nome mais explicativo para as branches, mas nos adaptamos melhor a este formato simples.

# Commits

Ao criar um commit devemos sempre pensar em nós mesmos navegando pelo histórico, tentando identificar onde determinada modificação foi realizada no projeto. 

O Jean Carlo Machado tem um tópico do seu [curso de Git](https://www.temporealeventos.com.br/git-e-github-curso-presencial) (recomendo fortemente!) que fala sobre isso. Vou destacar aqui alguns pontos que acho importantes:

- Escreva na forma imperativa. Ex: **ajuste de estilo no formulário X** ao **invés de ajustado estilo no formulário X**

- Se é difícil dar nomes talvez seja melhor quebrar o commit antes.

- Mais commits é melhor que menos commits.

- Commits não deveriam quebrar o build (serem atômicos).

- Commits não deveriam necessitar mais de 5 a 10 minutos para serem compreendidos e revisados

Boas práticas de comunicação devem ser exercitadas e aprimoradas no dia a dia de cada projeto e equipe. O objetivo deste post não é apenas sugerir as soluções que estamos usando, mas sim instigar o leitor a olhar para seu processo e identificar pontos de melhoria, discuti-los com os times e criar um ambiente de melhoria constante. 

Se você tem outras sugestões por favor compartilhe! 
