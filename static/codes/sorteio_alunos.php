<?php
srand((float) microtime() * 10000000);//inicializa a semente aleatória
$grupos = array(
 array("Neo", "Morpheus", "Trinity", "Cypher", "Tank"), //cada array é um grupo
 array("Clark", "Bruce", "Diana")
);
foreach($grupos as $g) {
 $rand_keys = array_rand($g, 1);
 echo $g[$rand_keys] . "\n";
}
?>