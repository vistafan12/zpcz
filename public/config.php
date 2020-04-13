<?php
// another config bcoz why not
$db_name = '';
$db_user = '';
$db_pass = '';
$db_host = '';
$db_host2 = $db_host;
$db_name2 = $db_name;
$db_user2 = $db_user;
$db_pass2 = $db_pass;
$PDOdbconn = new PDO("mysql:host=". $db_host .";dbname=". $db_name ."",$db_user,$db_pass);
?>
