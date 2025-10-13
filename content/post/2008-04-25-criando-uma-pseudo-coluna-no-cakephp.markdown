---
categories:
- cakephp
- codes
comments: true
date: 2008-04-25T10:48:42Z
slug: criando-uma-pseudo-coluna-no-cakephp
tags:
- cakephp
- php
title: Criando uma pseudo-coluna no CakePHP
url: /2008/04/25/criando-uma-pseudo-coluna-no-cakephp/
wordpress_id: 265
---

Estou desenvolvendo um sistema grande usando o framework CakePHP e surgiu uma necessidade. Precisava criar uma pseudo-coluna com o resultado do cálculo de outras duas. Seguindo o conceito de MVC, achei mais interessante fazer este cálculo no Model para poder usar em todos os programas que utiizam aquela tabela. 

Para ilustrar isto montei um exemplo. Criei a seguinte tabela:

    
    
    CREATE TABLE IF NOT EXISTS `clientes` (
      `id` int(11) NOT NULL auto_increment,
      `nome` varchar(100) NOT NULL,
      `sobrenome` varchar(100) NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
    



O Model da tabela ficou da seguinte forma:

    
    
    class Cliente extends AppModel {
    
      var $name = 'Cliente';
      var $validate = array(
     	'id' => VALID_NOT_EMPTY,
     	'nome' => VALID_NOT_EMPTY,
     	'sobrenome' => VALID_NOT_EMPTY,
      );
    
      /*
      funcao que é executada toda vez que é realizado uma consulta na tabela
      esta funcao adiciona o nome completo do cliente ao resultado como uma pseudo-coluna.
      idéia tirada de http://www.paulherron.net/articles/view/cakephp_afterfind_psuedofield
      */
      function afterFind($results) {
         if(isset($results['0']['Cliente'])) {
           foreach ($results as $key => $val) {
             $results[$key]['Cliente']['nome_completo'] = $results[$key]['Cliente']['nome']  . $results[$key]['Cliente']['sobrenome'] ;
           }
        }
       return $results;
      }
    }
    



E na visão eu simplesmente imprimo a pseudo-coluna: 


    
    echo $cliente['Cliente']['nome_completo'];



Simples assim. 
