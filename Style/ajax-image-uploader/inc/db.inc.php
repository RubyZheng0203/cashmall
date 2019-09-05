<?php

$username = "root";
$password = "passwd";
$hostname = "localhost";	
$database = "fmjinrong";

mysql_connect($hostname, $username, $password) or die(mysql_error());
mysql_select_db($database) or die(mysql_error()); 

?>