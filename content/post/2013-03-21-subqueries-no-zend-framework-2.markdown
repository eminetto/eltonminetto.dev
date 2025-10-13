---
categories:
- zend framework 2
comments: true
date: 2013-03-21T00:00:00Z
title: Subqueries no Zend Framework 2
url: /2013/03/21/subqueries-no-zend-framework-2/
---

Essa dica veio do amigo [Romulo Busatto](http://www.facebook.com/romulo.busatto), de uma forma de criar subqueries usando o componente _Zend\Db_ do Zend Framework 2.

No _controller_ vamos usar o _Zend\Db\Sql_ para criar as consultas:

``` php
<?php
namespace Application\Controller;
 
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

/**
 * Controlador exemplo
 * 
 * @category Application
 * @package Controller
 * @author  Elton Minetto <eminetto@coderockr.com>
 */
class IndexController extends AbstractActionController
{

    /**
     * Exemplo de uso de sub queries
     * @return void
     */
    public function indexAction()
    {
        //adapter configurado no ServiceManager
        $adapter = $this->getServiceLocator()->get('DbAdapter');
        $sql = new Sql($adapter);
        $mainSelect = $sql->select()->from('comments');
        $selectPost = $sql->select()
                          ->from('posts')
                          ->columns(array('title'))
                          ->where('id = post_id');
        $mainSelect->columns(
            array(
                'description',
                'name',
                'comment_date',
                'post_title' => new Expression('?',array($selectPost)),
                
            )
        );        

        $statement = $sql->prepareStatementForSqlObject($mainSelect);
        $comments = $statement->execute();
        
        return new ViewModel(array(
            'comments' => $comments
        ));

    }
}

```
A consulta que será gerada, no MySQL, é:

``` sql
SELECT "comments"."description" AS "description", 
       "comments"."name" AS "name", 
       "comments"."comment_date" AS "comment_date", 
      (SELECT "posts"."title" AS "title" FROM "posts" WHERE id = post_id) AS "post_title" 
FROM "comments"
```

E finalmente, na _view_ vamos ter acesso aos dados e podemos usá-los normalmente:


``` php
<?php foreach ($this->comments as $c): ?>
  <?php echo $c['post_title'];?> -  <?php echo $c['description'];?> - <?php echo $c['comment_date'];?> <br>

<?php endforeach;?>
```

Esse é um exemplo bem simples, mas dá para expandir e criar coisas mais complexas com isso.

Mais informações sobre o _Zend\Db\Sql_ podem ser encontradas no [manual do framework](http://framework.zend.com/manual/2.1/en/modules/zend.db.sql.html) e se você quer aprender mais sobre o Zend Framework 2 confira o curso no [Code Squad](http://code-squad.com/curso/zf2-na-pratica) ou o e-book [Zend Framework 2 na prática](http://www.zfnapratica.com.br)

