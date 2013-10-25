<?php
require('lib/SQLManager.php');

function db(){
	$dbc = new SQLManager('localhost','MYSQL','meeting','root','meeting');
	return $dbc;
}

?>
