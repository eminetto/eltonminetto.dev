---
categories: null
comments: true
date: 2016-06-24T00:00:00Z
title: Como melhorar seus códigos usando Object Calisthenics
url: /2016/06/24/como-melhorar-seus-codigos-usando-object-calisthenics/
---

Em um dos primeiros projetos que a Coderockr participou tivemos o privilégio de trabalhar com um "dream team": [Eduardo Shiota](http://eshiota.com), [Guilherme Blanco](https://twitter.com/guilhermeblanco), [Rafael Dohms](http://doh.ms) e [Otavio Ferreira](http://otaviofff.me) (em ordem alfabética porque é impossível perfilá-los em qualquer ordem de relevância). 

Neste projeto foi possível aprimorarmos vários pontos importantes como TDD, Scrum, trabalho remoto, análise, integração contínua, etc. Mas o que mais me marcou foram os conceitos de Clean Code e Object Calisthenics que eram aplicados ao projeto. 

<!--more-->

O Object Calisthenics é uma série de boas práticas e regras de programação que foram criadas pela comunidade de desenvolvedores Java. O Guilherme e o Rafael foram os responsáveis por adaptar estas regras para o ambiente PHP e são grandes evangelizadores destes conceitos. Se você tiver a oportunidade de ver alguma palestra deles sobre o assunto eu recomendo fortemente. Mas como eles estão vivendo fora do Brasil você pode começar olhando os slides da palestra [PHP para Adultos: Clean Code e Object Calisthenics](http://www.slideshare.net/guilhermeblanco/php-para-adultos-clean-code-e-object-calisthenics) e [You code sucks, let's fix it](http://www.slideshare.net/rdohms/bettercode-phpbenelux212alternate).

Eu lembrei deste tópico esta semana, quando me deparei com o seguinte código em um projeto que estou dando manutenção/desenvolvendo novas features:

```php
<?php

public function unsubscribe($user, $category) {

    // Se o usuário pode ser bloqueado
    if ($this->isUserLockable($user)) {
        // Se a categoria pode ser bloqueada
        if ($this->isCategoryLockable($category)) {
            // Se essa opção não foi executada ainda, ou seja, usuário e categoria não estão bloqueados ainda
            if (!$this->isUserAndCategoryLocked($user, $category)) {

                return [
                    'type' => 'success',
                    'message' => 'Pronto! Você não receberá mais ' . $category['name'] . ' de ' . $user['name'] . '. Para reativar o recebimento dessas mensagens, <a href="' . $this->getRequest()->getBasePath() . '/messages/setting">acesse suas configurações</a>'
                ];
            } else {

                return [
                    'type' => 'success',
                    'message' => 'Você não está recebendo ' . $category['name'] . ' de ' . $user['name'] . '.'
                ];
            }
        } else {

            return [
                'type' => 'error',
                'message' => 'Algo deu errado. A categoria ' . $category['name'] . ' parece não ser passível de bloqueio.'
            ];
        }
    } else {
        return [
            'type' => 'error',
            'message' => 'Algo deu errado. O usuário ' . $user['name'] . ' parece não ser passível de bloqueio.'
        ];
    }
}
```

É um código totalmente funcional mas analisando algumas regras do Object Calisthenics é possível refatorá-lo para algo assim:

```php

<?php

public function unsubscribe($user, $category) {

        if (!$this->isUserLockable($user)) {
            return [
                'type' => 'error',
                'message' => 'Algo deu errado. O usuário ' . $user['name'] . ' parece não ser passível de bloqueio.'
            ];
        }
        if (!$this->isCategoryLockable($category)) {
            return [
                'type' => 'error',
                'message' => 'Algo deu errado. A categoria ' . $category['name'] . ' parece não ser passível de bloqueio.'
            ];
        }
        if (!$this->isUserAndCategoryLocked($user, $category)) {
            return [
                'type' => 'success',
                'message' => 'Pronto! Você não receberá mais ' . $category['name'] . ' de ' . $user['name'] . '. Para reativar o recebimento dessas mensagens, <a href="' . $this->getRequest()->getBasePath() . '/messages/setting">acesse suas configurações</a>q'
            ];
        }

        return [
            'type' => 'success',
            'message' => 'Você não está recebendo ' . $category['name'] . ' de ' . $user['name'] . '.'
        ];
    }

```

Desta forma o código fica mais legível e de fácil manutenção. E o código torna-se mais performático pois possui uma complexidade bem menor. 

Minha recomendação é revisar as dicas do Guilherme e do Rafael e tentar aplicá-las aos seus códigos. Ou pelo menos tentar aos poucos absorvê-las e adaptá-las a sua realidade. O resultado é muito recompensador e vale o pequeno investimento.