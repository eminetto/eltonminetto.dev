---
categories: projetos
comments: true
date: 2014-01-02T00:00:00Z
title: Gerenciando projetos com Github e ZenHub
url: /2014/01/02/gerenciando-projetos-com-github-e-zenhub/
---

Eu sempre gostei da “abordagem Unix”: pequenas  ferramentas que fazem apenas uma coisa bem feita mas que podem ser usadas em conjunto com outras para criarmos um ambiente poderoso.

Venho usando essa abordagem para quase tudo, incluindo o gerenciamento de projetos na [Coderockr](http://coderockr.com). Usamos algumas ideias de Scrum e Kanban incluindo sprints, pontos de complexidade, quadro de tarefas, burndown chart, reuniões diárias, etc.  Atualmente usamos uma série de aplicativos diferentes para cada uma das fases:

- Trello para gerenciar o quadro de tarefas e sprints. Usamos uma extensão do Firefox/Chrome chamada [Scrum for Trello](http://scrumfortrello.com/) para contabilizar os pontos de complexidade:

[![](/images/posts/scrum_for_trello.png)](/images/posts/scrum_for_trello.png)

- Github para armazenar os códigos
- Google Docs para criar o burndown chart:

[![](/images/posts/burndown.png)](/images/posts/burndown.png)

Tudo funciona bem mas percebi que alguns membros da equipe estavam criando _issues_ do Github para cada tarefa criada no Trello porque assim eles conseguem vincular facilmente os _commits_ de código com a tarefa, o que faz muito sentido. Observando isso cheguei a hipótese que o Trello não é necessário para gerenciarmos os nossos projetos de software e que podemos usar apenas o Github para este fim.  Continuamos usando o Trello para gerenciarmos outros projetos que não são especificamente de software, como marketing ou mesmo as tarefas diárias da empresa (compras, idas ao banco, etc).

Para isso criei a seguinte lógica:

- cada sprint do projeto torna-se um _milestone_ no Github;
- cada tarefa do _backlog_ torna-se uma _issue_ no Github;
- cada ponto de complexidade (usando a escala de _fibonacci_) torna-se um _label_ que é aplicado a cada _issue_;

O primeiro passo foi criar as _labels_ equivalentes aos pontos de complexidade:

[![](/images/posts/labels.png)](/images/posts/labels.png)

Podemos manter as outras _labels_ e usá-las junto com estas novas.

Agora é preciso criar os _milestones_ que irão representar os sprints do projeto:

[![](/images/posts/sprints.png)](/images/posts/sprints.png)

Cada _milestone_ pode ter uma data de vencimento, o que condiz bastante com o comportamento dos sprints, que geralmente são períodos de uma ou duas semanas.  Nesta tela também é possível visualizarmos o andamento de cada sprint, pois o Github nos mostra o percentual de trabalho já realizado (_issues_ fechadas).

Podemos agora criar as nossas _issues_, anexar as devidas _labels_ representando os pontos de complexidade e alocá-las para cada sprint.

Uma característica interessante do Trello é a facilidade de acompanharmos o andamento das tarefas de uma forma bem visual, mostrando-as em um quadro com divisões para cada fase. Podemos suprir essa característica usando o [ZenHub](https://www.zenhub.io/). Trata-se de um complemento para o Chrome (com uma versão para Firefox já prometida) que adiciona mais funcionalidades ao Github, entre elas um quadro bem parecido com o fornecido pelo Trello.

Após instalar o complemento, acessar a página do repositório pela primeira vez e clicar na opção “Board” o ZenHub mostra uma tela sugerindo uma organização de quadros, ou permitindo que você crie a sua própria:

[![](/images/posts/ZenHub_1.png)](/images/posts/ZenHub_1.png)

Eu criei a seguinte organização para nossos projetos:

[![](/images/posts/ZenHub_2.png)](/images/posts/ZenHub_2.png)

Com isso podemos facilmente mover as _issues_ entre as fases e acompanhar o andamento do projeto.

Outra vantagem em usarmos o Github para gerenciar os projetos é que podemos usar a sua API para criarmos relatórios, inclusive um burndown chart. Criei um pequeno [script](https://gist.github.com/eminetto/8224766) para testes, usando a [php-github-api](https://github.com/KnpLabs/php-github-api):

``` php
<?php
require_once 'vendor/autoload.php';

if ($argc < 2) {
    echo "Usage: php index.php ProjectName\n";
    exit;
}
$projectName = $argv[1];

$client = new \Github\Client(
    new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
);

$client->authenticate('username', 'password', \Github\Client::AUTH_HTTP_PASSWORD);

//get sprints
$sprints = array();
$milestones = $client->api('issue')->milestones()->all('coderockr', $projectName, array('direction' => 'asc'));
foreach ($milestones as $m) {
    $sprints[$m['id']] = array(
        'name' => $m['title'],
        'open_issues' => $m['open_issues'],
        'closed_issues' => $m['closed_issues'],
        'state' => $m['state'],
        'due_on' => $m['due_on'],
        'open_points' => 0,
        'closed_points' => 0,
    );
}
//get issues
$issues = array_merge(
    $client->api('issue')->all('coderockr', $projectName, array('state' => 'open')),
    $client->api('issue')->all('coderockr', $projectName, array('state' => 'closed'))
);
//update sprint points
foreach ($issues as $i) {
    $milestone = $i['milestone'];
    switch ($i['state']) {
        case 'open':
            $open_points = $sprints[$milestone['id']]['open_points'];
            foreach ($i['labels'] as $l) {
                $open_points += (float) $l['name'];
            }
            $sprints[$milestone['id']]['open_points'] = $open_points;
            break;
        case 'closed':
            $closed_points = $sprints[$milestone['id']]['closed_points'];
            foreach ($i['labels'] as $l) {
                $closed_points += (float) $l['name'];
            }
            $sprints[$milestone['id']]['closed_points'] = $closed_points;
            break;
    }

}
//show sprint's details
$total_open_points = 0;
$total_closed_points = 0;
foreach ($sprints as $s) {
    $sprint_points = $s['open_points'] + $s['closed_points'];
    $sprint_issues = $s['open_issues'] + $s['closed_issues'];
    echo "{$s['name']} due on {$s['due_on']} has {$s['closed_issues']} closed of {$sprint_issues} issues and {$s['closed_points']} closed of {$sprint_points} points\n";
    $total_open_points += $s['open_points'];
    $total_closed_points += $s['closed_points'];
}
//show project's details
$project_points = $total_open_points + $total_closed_points;
echo "Project have {$total_closed_points} closed of {$project_points} points \n";
```

Executando na linha de comando:

	eminetto@MacBook-Pro-de-Elton ~/Documents/Projects/GitHubPM: php index.php ProjectTemplate
	Sprint 1 due on 2014-01-09T08:00:00Z has 1 closed of 3 issues and 2 closed of 28 points
	Sprint 2 due on 2014-01-16T08:00:00Z has 1 closed of 2 issues and 3 closed of 11 points
	Sprint 3 due on 2014-01-23T08:00:00Z has 0 closed of 2 issues and 0 closed of 3.5 points
	Sprint 4 due on 2014-01-30T08:00:00Z has 0 closed of 1 issues and 0 closed of 3 points
	Project have 5 closed of 45.5 points
	eminetto@MacBook-Pro-de-Elton ~/Documents/Projects/GitHubPM:

É somente um exemplo mas brincando um pouco com a API do Github é possível fazermos controles bem avançados, gráficos, aplicativos móveis, etc.

Ainda vamos testar essa abordagem em alguns projetos e algumas coisas podem mudar, assim como o ZenHub e o próprio Github podem criar novas funcionalidades num futuro próximo, então esse post deve receber atualizações.  Se você tiver sugestões ou experiências parecidas por favor compartilhe nos comentários.
