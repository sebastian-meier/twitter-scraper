<?php

header('Content-type: text/plain;charset=utf8');

require_once ('../config.php');
require_once ('../lib/db.php');

$results = query_mysql('SELECT id FROM '.$db_prefix.'twitter_requests WHERE request = "'.$_GET["request"].'"', $link);
while ($row = mysql_fetch_assoc($results)) {
    $id =  $row["id"];
}

$results = query_mysql('SELECT field_value FROM '.$db_prefix.'twitter_tweetmetadata WHERE request_id = "'.$id.'" AND field_key = "created_at" ORDER BY twitter_tweet_id ASC LIMIT 0, 1', $link);
while ($row = mysql_fetch_assoc($results)) {
    $d1 =  new DateTime($row["field_value"]);
}

$results = query_mysql('SELECT field_value FROM '.$db_prefix.'twitter_tweetmetadata WHERE request_id = "'.$id.'" AND field_key = "created_at" ORDER BY twitter_tweet_id DESC LIMIT 0, 1', $link);
while ($row = mysql_fetch_assoc($results)) {
    $d2 = new DateTime($row["field_value"]);
}

$diff = $d1->diff($d2);
echo $diff->d.' days'."\n";
echo $diff->h.' hours'."\n";
echo $diff->i.' minutes'."\n";
echo $diff->s.' seconds'."\n"."\n";

$results = query_mysql('SELECT * FROM '.$db_prefix.'twitter_tweets', $link);
echo mysql_num_rows($results).' tweets'."\n"."\n";

$results = query_mysql('SELECT * FROM '.$db_prefix.'twitter_users', $link);
echo mysql_num_rows($results).' users'."\n\n\n";

?>

