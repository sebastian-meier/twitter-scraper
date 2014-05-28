<?php

header('Content-type: text/plain;charset=utf8');

require_once ('../codebird.php');
require_once ('../db.php');
require_once ('../'.$_GET['city'].'/config.php');

//Initiate Codebird with consumer-key and -secret
\Codebird\Codebird::setConsumerKey($key, $secret);
$cb = \Codebird\Codebird::getInstance();

$border = 413805278040432640;
$smallest = 413805265448751104;

$next_big = 413805704345321472;
$next_smallest = 413805691191980033;

$s = 413805676519886848;

$reply = $cb->search_tweets(array('geocode'=>$location, 'count'=>'100', 'max_id' => 413805441437941760));

$dif = $reply->statuses[(count($reply->statuses)-1)]->id_str - $smallest;

echo $reply->statuses[0]->id_str;
echo "\n";
echo $reply->statuses[(count($reply->statuses)-1)]->id_str;
echo "\n";
echo count($reply->statuses);
echo "\n";
echo $dif;

?>