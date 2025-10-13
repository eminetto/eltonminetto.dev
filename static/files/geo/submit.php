<?php
/*** mysql hostname ***/
$hostname = 'localhost';

/*** mysql username ***/
$username = 'root';

/*** mysql password ***/
$password = 'cr123456';

try {
    $dbh = new PDO("mysql:host=$hostname;dbname=geo", $username, $password);
    /*** echo a message saying we have connected ***/
    }
catch(PDOException $e)
    {
    echo $e->getMessage();
}
$place_id = $_POST['place_id'];
$text = $_POST['textarea'];
$sql = "insert into content values (null, ".$place_id.", 'Tip', '".$text."',now(),null,'Tip')";
$dbh->exec($sql);
?>
