<?php

header('Content-type: text/plain;charset=utf8');

require_once ('codebird.php');
require_once ('db.php');

$cities = array("amsterdam", "berlin", "capetown", "detroit", "dublin", "london", "mexicocity", "newyork", "paris", "sanfrancisco", "stockholm", "sydney", "tokyo", "washington");

$current = 13;

require_once ($cities[$current].'/config.php');

\Codebird\Codebird::setConsumerKey($key, $secret);
$cb = \Codebird\Codebird::getInstance();

session_start();

if($_GET['oauth_request']=='true'){

    $reply = $cb->oauth_requestToken(array('oauth_callback' => 'http://twitter.sebastianmeier.eu/1-create_tokens.php'));
    $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
    $auth_url = $cb->oauth_authorize();
    header('Location: ' . $auth_url);
    die();

}elseif(isset($_GET['oauth_verifier'])){

    $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $reply = $cb->oauth_accessToken(array('oauth_verifier' => $_GET['oauth_verifier']));
    $sql = 'INSERT INTO `twitter_token` (`token`, `secret`, `city`)VALUES("'.$reply->oauth_token.'", "'.$reply->oauth_token_secret.'", "'.$cities[$current].'")';
    $insert = query_mysql($sql, $link);
    mysql_free_result($insert);

}

echo "good";

?>