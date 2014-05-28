<?php

header('Content-type: text/plain;charset=utf8');

require_once ('db.php');

$results = sql_request('SELECT field_value FROM twitter_tweetmetadata WHERE field_key = ? ORDER BY twitter_tweet_id ASC LIMIT 0, 1', 'created_at');
while($result = $results->fetchRow()) {
    $d1 =  new DateTime($result[0]);
}

$results = sql_request('SELECT field_value FROM twitter_tweetmetadata WHERE field_key = ? ORDER BY twitter_tweet_id DESC LIMIT 0, 1', 'created_at');
while($result = $results->fetchRow()) {
    $d2 = new DateTime($result[0]);
}

$results = sql_request('SELECT timestamp FROM twitter_log ORDER BY id ASC LIMIT 0, 1', false);
while($result = $results->fetchRow()) {
    $d3 =  new DateTime($result[0]);
}

$results = sql_request('SELECT timestamp FROM twitter_log ORDER BY id DESC LIMIT 0, 1', false);
while($result = $results->fetchRow()) {
    $d4 = new DateTime($result[0]);
}

$diff = $d1->diff($d2);
$diff_r = $d3->diff($d4);
echo $diff->d.' days'."\n";
echo $diff->h.' hours'."\n";
echo $diff->i.' minutes'."\n";
echo $diff->s.' seconds'."\n"."\n";

$results = sql_request('SELECT * FROM twitter_tweets', false);
echo $results->numRows().' tweets'."\n"."\n";

$results = sql_request('SELECT * FROM twitter_users', false);
echo $results->numRows().' users'."\n\n\n";

echo 'req run'."\n";

echo $diff_r->d.' days'."\n";
echo $diff_r->h.' hours'."\n";
echo $diff_r->i.' minutes'."\n";
echo $diff_r->s.' seconds'."\n"."\n";


?>

