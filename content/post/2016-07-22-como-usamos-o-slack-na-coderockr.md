+++
bigimg = ""
date = "2016-07-22T11:01:44-03:00"
subtitle = ""
title = "Como usamos o Slack na Coderockr"

+++

Dentre os valores da [Coderockr](http://coderockr.com) estão dois pontos importantes: cooperação e transparência. Como somos uma empresa que desenvolve software nada mais esperado do que usarmos aplicativos para ajudar nestes objetivos. E hoje a principal ferramenta para isso é o Slack, que rapidamente tornou-se o coração de muitas empresas

<!--more-->

# Canais e organizações

Nós usamos o Slack como o nosso canal de comunicação, tanto entre as equipes quanto com os clientes. Cada projeto recebe um canal específico, como no exemplo abaixo:

[![slack_coderockr](/images/posts/slack_coderockr.png)](/images/posts/slack_coderockr.png)

Os canais que eu ocultei são relativos a projetos de clientes, já os canais "planrockr" e "timerockr" são projetos da Coderockr. Um destaque aqui é o canal "exceptions" onde todos os projeto enviam os erros que acontecem. Vou comentar mais sobre isso abaixo.

Projetos maiores ganham uma organização separada:

[![slack_cliente](/images/posts/slack_cliente.png)](/images/posts/slack_cliente.png)

Neste exemplo dividimos melhor os assuntos entre as equipes de "backend", "frontend", "testing" e por localização, como a "joinville", já que temos equipes trabalhando em outras cidades. No canal "devops" encontram-se as mensagens geradas pelas ferramentas de teste e deploy que vou citar abaixo.

# Integrações

A parte da colaboração é fácil de entender no Slack, mas e quanto ao ponto "transparência" que eu comentei lá no começo? Nós usamos o Slack para centralizar todos os fatos relevantes que acontecem nos projetos, através de integração de ferramentas. Alguns exemplos de integrações que usamos:

- Jira envia para o canal #devops a criação de novas tarefas, a mudança de status (To Do, Doing, Done, etc). O mesmo para os [projetos onde usamos o Trello](https://www.youtube.com/watch?v=oybYF8XhXjs&index=2&list=PLkS5lYehKysZhw1prsoZQhiVfbYA5fEGz)
- Github (ou o Bitbucket) envia para o canal #devops (ou o canal do projeto) a criação de Pull Requests, commits, [branches](https://www.youtube.com/watch?v=0JDFcT3uCSs&index=3&list=PLkS5lYehKysZhw1prsoZQhiVfbYA5fEGz)
- O DeployBot envia para o #devops o resultado do [deploy](https://www.youtube.com/watch?v=5fiVaCszbDs&index=7&list=PLkS5lYehKysZhw1prsoZQhiVfbYA5fEGz) para um dos ambientes como homolog ou produção
- O [Codacy](https://www.youtube.com/watch?v=0sVeYpUiJig&index=4&list=PLkS5lYehKysZhw1prsoZQhiVfbYA5fEGz) envia para o #devops a nota do commit que o desenvolvedor acabou de enviar para o repositório
- O Buildkite avisa, também em #devops ou no canal do projeto, o resultado do [build](https://www.youtube.com/watch?v=TpeOwCzsVKg&index=6&list=PLkS5lYehKysZhw1prsoZQhiVfbYA5fEGz) de determinada funcionalidade
- Algum erro acontece em um dos servidores e este erro é enviado direto para o canal #exceptions. Como a maioria dos nossos servidores usa PHP usamos o componente Monolog para fazer isso. Apresentei uma [palestra](https://eltonminetto/dev/files/talks/letslog-160521130255.pdf) sobre isso recentemente

Também é possível criar comandos dentro do Slack para que ele execute tarefas para você:

[![slack_deploy](/images/posts/slack_deploy.png)](/images/posts/slack_deploy.png)

Rapidamente o Slack se tornou uma ferramenta indispensável para nós e temos ótimos resultados com ela. E você, como usa a ferramenta? Quer compartilhar outros truques e dicas?

Se você quer saber mais detalhes sobre a metodologia de trabalho que criamos na Coderockr confira nossa série de videos sobre o "[Coderockr Way](https://www.youtube.com/playlist?list=PLkS5lYehKysZhw1prsoZQhiVfbYA5fEGz)". E se procura uma ferramenta para tornar seus projetos ainda mais inteligentes não deixe de conferir o [http://planrockr.com](http://planrockr.com)
