<?php

require_once ('../config.php');
require_once ('../lib/codebird.php');
require_once ('../lib/db.php');

//Initiate Codebird with consumer-key and -secret
\Codebird\Codebird::setConsumerKey($key, $secret);
$cb = \Codebird\Codebird::getInstance();

//load user-token and -secret from database
$sql = 'SELECT `token`, `secret` FROM `'.$db_prefix.'twitter_token` LIMIT 1';
$result = query_mysql($sql, $link);
while ($row = mysql_fetch_assoc($result)) {
	$cb->setToken($row["token"], $row["secret"]);
}
mysql_free_result($result);

//load request-id, current page and check if we are already done here
$sql = 'SELECT `id`, `done`, `pause` FROM `'.$db_prefix.'twitter_requests` WHERE request = "'.$_GET["request"].'" LIMIT 1';
$result = query_mysql($sql, $link);
while ($row = mysql_fetch_assoc($result)) {
	$request_id = $row["id"];
	$done = $row["done"];
	$pause = $row["pause"];
}
mysql_free_result($result);

$g = 0;

if($pause<1){
	//Depending on limitation we can run multiple rounds
	//As we are running a couple of other queries at the
	//same time, we can only do it once
	for($i=0; $i<3; $i++){
		//Create the user-id string
		$users = "";

		$sql = 'SELECT `id` FROM `'.$db_prefix.'twitter_users` WHERE done = 0 AND request_id = '.$request_id.' LIMIT 100';
		$result = query_mysql($sql, $link);
		while ($row = mysql_fetch_assoc($result)) {
			if($users != ""){ $users .= ","; }
			$users .= $row["id"];
		}
		mysql_free_result($result);

		$reply = $cb->users_lookup(array('user_id'=>$users, 'include_entities'=>'true'));

		$g += processResults($reply, $link, $request_id, $_GET["request"]);
	}

	echo 'users:'.$_GET["request"].':'.$g;
}else{
	echo 'pause';
}

function processResults($results, $link, $request_id, $request){
	global $db_prefix;
	$c = 0;

	foreach ($results as $result) {
		if(isset($result->id_str)){
	
			//Store all values of the object as metadata, beside the user-object
			foreach($result as $key => $value){
				goDeeper("", $key, $value, $link, $request_id, $request, $result);
			}

			$sql = 'UPDATE `'.$db_prefix.'twitter_users` SET `done` = 1 WHERE id = '.$result->id_str;
			$update = query_mysql($sql, $link);
			$c++;

		}
	}

	return $c;
}

function goDeeper($parent, $key, $value, $link, $request_id, $request, $result){
	global $db_prefix;
	if(!is_object($value) && !is_array($value) && $key != "status"){
		$sql = 'INSERT INTO `'.$db_prefix.'twitter_usermetadata` (`twitter_user_id`, `field_key`, `field_value`, `request_id`)VALUES("'.$result->id_str.'", "'.$parent.$key.'", "'.str_replace('"', "'", $value).'", '.$request_id.')';
		$insert = query_mysql($sql, $link);
	}else if((is_object($value) || is_array($value)) && $key != "status"){
		foreach ($value as $inner_key => $inner_value) {
			goDeeper($key." ", $inner_key, $inner_value, $link, $request_id, $request, $result);
		}
	}
}

?>