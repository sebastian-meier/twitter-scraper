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


for($i=0; $i<6; $i++){
	$g = 0;
	$next = "";

	//load request-id, current page and check if we are already done here
	$sql = 'SELECT `id`, `since_id`, `max_id`, `temp_since_id`, `cycle`, `done`, `pause`, `overall` FROM `twitter_requests` WHERE city = "'.$city.'" LIMIT 1';
	$result = query_mysql($sql, $link);
	while ($row = mysql_fetch_assoc($result)) {
		$request_id = $row["id"];
		$cycle = $row["cycle"];
		$since_id = $row["since_id"];
		$temp_since_id = $row["temp_since_id"];
		$max_id = $row["max_id"];
		$done = $row["done"];
		$pause = $row["pause"];
		$overall = $row["overall"];
	}
	mysql_free_result($result);

	if($pause<1){

		if($cycle=="new"){
			//This is the state at the start of the scraping-run
			$reply = $cb->search_tweets(array('geocode'=>$location, 'count'=>'100'));
			$result_since = processResults($reply, $link, $request_id, $city);
			$g += $result_since[2];

			//store the since_id and set the cycle to start
			$sql = 'UPDATE `twitter_requests` SET `cycle` = "start", `since_id` = "'.$result_since[0].'", `last` = '.$g.', `overall` = '.(intval($g)+intval($overall)).' WHERE id = '.$request_id;
			$update = query_mysql($sql, $link);
			$next = "start";

		}elseif($cycle=="start"){
			//We have the last tweet-id from our previous call which we are using as a since_id parameter
			$reply = $cb->search_tweets(array('geocode'=>$location, 'count'=>'100'));
			$result_since = processResults($reply, $link, $request_id, $city);
			$g += $result_since[2];

			if($result_since[1]<$since_id){
				//Since our last request there were not more than 100 tweets so we can repeat this step on the run
				$sql = 'UPDATE `twitter_requests` SET `cycle` = "start", `time` = "'.$result_since[4].'", `since_id` = "'.$result_since[0].'", `last` = '.$g.', `overall` = '.(intval($g)+intval($overall)).' WHERE id = '.$request_id;
				$update = query_mysql($sql, $link);
				$next = "start";

			}else{
				//Most likely there were too much tweets since our last request so we need to fill the gap on our next run
				//We will store our entry tweet id as the next since_id in temp_since_id
				//We will also store the biggest current id as our max_id
				$sql = 'UPDATE `twitter_requests` SET `cycle` = "active", `time` = "'.$result_since[4].'", `temp_since_id` = "'.$result_since[0].'", `max_id` = "'.$result_since[1].'", `last` = '.$g.', `overall` = '.(intval($g)+intval($overall)).' WHERE id = '.$request_id;
				$update = query_mysql($sql, $link);
				$next = "active";

			}
		}elseif($cycle=="active"){
			$reply = $cb->search_tweets(array('geocode'=>$location, 'count'=>'100', 'max_id' => $max_id));
			$result_since = processResults($reply, $link, $request_id, $city);
			$g += $result_since[2];

			if($result_since[1]<$since_id){
				//When we receive less then 100 we have closed the gap, so we can start over from the beginning of the gap, 
				//stored in our temp_since_id variable
				$sql = 'UPDATE `twitter_requests` SET `cycle` = "start", `since_id` = "'.$temp_since_id.'", `last` = '.$g.', `overall` = '.(intval($g)+intval($overall)).' WHERE id = '.$request_id;
				$update = query_mysql($sql, $link);
				$next = "start";

			}else{
				//It seems we haven't reached the end of our gap yet, so we need to do it over again
				$sql = 'UPDATE `twitter_requests` SET `cycle` = "active", `max_id` = "'.$result_since[1].'", `last` = '.$g.', `overall` = '.(intval($g)+intval($overall)).' WHERE id = '.$request_id;
				$update = query_mysql($sql, $link);
				$next = "active";

			}	
		}
		echo 'tweets:'.$city.':'.$g.'/'.$result_since[3].':'.$cycle."=>".$next." (".$result_since[0]."/".$result_since[1]." = ".($result_since[1]-$since_id).")\n";
	}else{
		echo 'tweets:'.$city.':pause'."\n";
	}
}

//Processing the results from the twitter API
function processResults($results, $link, $request_id, $city){
	$since_id_str = 0;
	$max_id_str = 9999999999999999999999999999999999;
	$insert_count = 0;
	$time = 0;
	$duplicate_count = 0;
	foreach ($results->statuses as $result) {
		if(isset($result->id_str)){

			//store the ids for the next run
			$idstr = $result->id_str;
			if($since_id_str<$idstr){
				$since_id_str = $idstr;
				$time = $result->created_at;
			}

			if($max_id_str>$idstr){
				$max_id_str = $idstr;
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

				//Inserted one. yay.
				$insert_count++;

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
			}else{
				$duplicate_count++;
			}
		}
	}
	return array($since_id_str, $max_id_str, $insert_count, $duplicate_count, $time);
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