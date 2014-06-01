<?php

	include("config.php");
	require_once('lib/codebird.php');
	include("lib/db.php");

    foreach ($requests as $key => $request) {
    	$sql = 'INSERT INTO `'.$db_prefix.'twitter_requests` (`request`, `page`, `done`)VALUES("'.$key.'", 1, 0)';
    	$insert = query_mysql($sql, $link);
    	mysql_free_result($insert);
	}
	
	echo "Setup is done. You can now start setting up your Cron-Job or Browser based Scraping.";

?>