<?php

require_once ('db.php');

$cities = array("amsterdam", "berlin", "capetown", "detroit", "dublin", "london", "mexicocity", "newyork", "paris", "sanfrancisco", "stockholm", "sydney", "tokyo", "washington");

foreach ($cities as $tcity) {
	require_once ($tcity.'/config.php');
	$sql = 'INSERT INTO `twitter_requests` (`request`, `page`, `done`, `city`)VALUES("'.$location.'", 1, 0, "'.$city.'")';
    $insert = query_mysql($sql, $link);
    mysql_free_result($insert);
}

echo "good";

?>