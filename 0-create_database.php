<?php

	include("config.php");
	require_once('lib/codebird.php');
	include("lib/db.php");

	$sql = array();
	array_push($sql, "CREATE TABLE IF NOT EXISTS `'.$db_prefix.'twitter_tweetmetadata` (`id` int(11) NOT NULL AUTO_INCREMENT,`twitter_tweet_id` bigint(20) NOT NULL,`field_key` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`field_value` text CHARACTER SET utf8 COLLATE utf8_bin,PRIMARY KEY (`id`))");
	array_push($sql, "CREATE TABLE IF NOT EXISTS `'.$db_prefix.'twitter_tweets` (`id` bigint(20) NOT NULL,`request_id` int(20) NOT NULL,PRIMARY KEY (`id`))");
	array_push($sql, "CREATE TABLE IF NOT EXISTS `'.$db_prefix.'twitter_usermetadata` (`id` int(11) NOT NULL AUTO_INCREMENT,`twitter_user_id` bigint(20) NOT NULL,`field_key` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`field_value` text CHARACTER SET utf8 COLLATE utf8_bin,PRIMARY KEY (`id`))");
	array_push($sql, "CREATE TABLE IF NOT EXISTS `'.$db_prefix.'twitter_users` (`id` bigint(20) NOT NULL,`request_id` bigint(20) NOT NULL,`done` tinyint(1) NOT NULL DEFAULT '0',PRIMARY KEY (`id`))");
	array_push($sql, "CREATE TABLE IF NOT EXISTS `'.$db_prefix.'twitter_log` (`id` int(11) NOT NULL AUTO_INCREMENT,`log` text COLLATE latin1_german2_ci,`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`id`))");
	array_push($sql, "CREATE TABLE IF NOT EXISTS `'.$db_prefix.'twitter_requests` (`id` int(11) NOT NULL AUTO_INCREMENT,`request` text CHARACTER SET utf8 NOT NULL,`since_id` bigint(20) NOT NULL,`done` tinyint(1) NOT NULL,`pause` tinyint(1) NOT NULL DEFAULT '0',`last` int(11) NOT NULL,`temp_since_id` bigint(20) NOT NULL,`max_id` bigint(20) NOT NULL,`overall` int(11) NOT NULL,`cycle` text CHARACTER SET utf8 NOT NULL,`time` text CHARACTER SET utf8 NOT NULL,PRIMARY KEY (`id`))");
	array_push($sql, "CREATE TABLE IF NOT EXISTS `'.$db_prefix.'twitter_token` (`id` int(11) NOT NULL AUTO_INCREMENT,`token` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`secret` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`))");

	foreach ($sql as $s) {
		$insert = query_mysql($s, $link);
    	mysql_free_result($insert);
	}

	echo 'Database Setup completed. <a href="1-create_tokens.php">Continue</a>';

?>