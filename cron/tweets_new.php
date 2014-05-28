<?php

header('Content-type: text/plain;charset=utf8');

require_once ('../codebird.php');
require_once ('../db.php');
require_once ('../'.$_GET['city'].'/config.php');

//Initiate Codebird with consumer-key and -secret
\Codebird\Codebird::setConsumerKey($key, $secret);
$cb = \Codebird\Codebird::getInstance();

//load user-token and -secret from database
$sql = 'SELECT `token`, `secret` FROM `twitter_token` WHERE city = "'.$city.'" LIMIT 1';
$result = query_mysql($sql, $link);
while ($row = mysql_fetch_assoc($result)) {
	$cb->setToken($row["token"], $row["secret"]);
}
mysql_free_result($result);

//load request-id, current page and check if we are already done here
$sql = 'SELECT `id`, `page`, `done`, `pause` FROM `twitter_requests` WHERE city = "'.$city.'" LIMIT 1';
$result = query_mysql($sql, $link);
while ($row = mysql_fetch_assoc($result)) {
	$request_id = $row["id"];
	$since = $row["page"];
	$done = $row["done"];
	$pause = $row["pause"];
}
mysql_free_result($result);

if($pause<1){
	$g = 0;

	//Depending on limitation we can run multiple rounds
	//As we are running a couple of other queries at the
	//same time, we can only do it once
	for($i=0; $i<6 && $since; $i++){

		if($since<=1){
			$reply = $cb->search_tweets(array('geocode'=>$location, 'count'=>'100'));
		}else{
			$reply = $cb->search_tweets(array('geocode'=>$location, 'count'=>'100', 'since_id' => $since));
		}

		//Log the query to twitter
		$sql = 'INSERT INTO `twitter_log` (`log`)VALUES("'.$location.',100,'.$since.'")';
		$insert = query_mysql($sql, $link);
		mysql_free_result($insert);

		//process the result and store it away
		$since = processResults($reply, $link, $request_id, $city);
		$g += count($reply->statuses);
	}

	if($since > 1){
		$sql = 'UPDATE `twitter_requests` SET `page` = "'.$since.'", `last` = '.$g.' WHERE id = '.$request_id;
		$update = query_mysql($sql, $link);
		mysql_free_result($update);
	}else{
		$since = "none";
	}
	echo 'tweets:'.$city.':'.$g."\n";
}else{
	echo 'pause';
}

//Processing the results from the twitter API
function processResults($results, $link, $request_id, $city){
	$since_id_str = false;
	foreach ($results->statuses as $result) {
		if(isset($result->id_str)){

			//store the id for the next run
			if(!$since_id_str){
				$since_id_str = $result->id_str;	
			}

			//check if this tweet already exists in the database
			$found = false;
			$sql = 'SELECT id FROM `'.$city.'_twitter_tweets` WHERE `id` = "'.$result->id_str.'"';
			$check = query_mysql($sql, $link);
			if(mysql_num_rows($check)>=1){
				$found = true;
			}
			mysql_free_result($check);

			//if it doesn't exist send it to the database
			if(!$found){
				$sql = 'INSERT INTO `'.$city.'_twitter_tweets` (`id`, `request_id`)VALUES("'.$result->id_str.'", '.$request_id.')';
				$insert = query_mysql($sql, $link);
				mysql_free_result($insert);

				//Store all values of the object as metadata, beside the user-object
				foreach($result as $key => $value){
					goDeeper("", $key, $value, $link, $request_id, $city, $result);
				}

				//Storing the user-id with the metadata for easier quering later
				$sql = 'INSERT INTO `'.$city.'_twitter_tweetmetadata` (`twitter_tweet_id`, `field_key`, `field_value`)VALUES("'.$result->id_str.'", "'.$key.'", "'.$value->id_str.'")';
				$insert = query_mysql($sql, $link);
				mysql_free_result($insert);

				//Check if the user has already been added to the database
				$ufound = false;
				$sql = 'SELECT `id` FROM `'.$city.'_twitter_users` WHERE `id` = "'.$result->user->id_str.'"';
				$check = query_mysql($sql, $link);
				if(mysql_num_rows($check)>=1){
					$ufound = true;
				}
				mysql_free_result($check);
				
				if(!$ufound){
					$sql = 'INSERT INTO `'.$city.'_twitter_users` (`id`, `request_id`)VALUES("'.$result->user->id_str.'", '.$request_id.')';
					$insert = query_mysql($sql, $link);
					mysql_free_result($insert);
				}
			}
		}
	}
	return $since_id_str;
}

function goDeeper($parent, $key, $value, $link, $request_id, $city, $rresult){
	if(!is_object($value) && !is_array($value) && $key != "user"){
		$sql = 'INSERT INTO `'.$city.'_twitter_tweetmetadata` (`twitter_tweet_id`, `field_key`, `field_value`)VALUES("'.$rresult->id_str.'", "'.$parent.$key.'", "'.str_replace('"', "'", $value).' ")';
		$insert = query_mysql($sql, $link);
		mysql_free_result($insert);						
	}else if((is_object($value) || is_array($value)) && $key != "user"){
		foreach ($value as $inner_key => $inner_value) {
			goDeeper($key." ", $inner_key, $inner_value, $link, $request_id, $city, $rresult);
		}
	}
}

?>