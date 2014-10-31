<?php

	include("config.php");
	require_once('lib/codebird.php');
	include("lib/db.php");

    foreach ($requests as $key => $request) {
    	$sql = 'INSERT INTO `'.$db_prefix.'twitter_requests` (`request`, `done`, `cycle`)VALUES("'.$key.'", 0, "new")';
    	$insert = query_mysql($sql, $link);
	}
	

	//!!! INSERT MASTER ROW
?>
<html><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8"></head><body>
<?php

	echo "Setup is done. You can now start setting up your Cron-Job or Browser based Scraping.";

?>
</body></html>