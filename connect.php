<?php
	require 'settings.php';
	$connection=mysql_connect($DBserver,$DBuser,$DBpassword) or die("con");
	$db=mysql_select_db($DBname) or die("db");
?>