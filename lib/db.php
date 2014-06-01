<?php

set_time_limit(30000);
ini_set("precision", 20);

if(!$link = mysql_connect($db_server, $db_user, $db_pass)){
    echo 'Problem connecting to the MySql Server';
    exit;
}

if(!mysql_select_db($db_database, $link)){
    echo 'Unable to connect to database-table ';
    exit;
}

function query_mysql($sql, $link){
	$result = mysql_query($sql, $link);
	if (!$result) {
    	echo "DB Error, could not execute request\n";
    	echo 'MySQL Error: ' . mysql_error();
    	exit;
	}else{
		return $result;
	}
}

mysql_query("SET NAMES 'utf8'");

?>