<?php

    include("config.php");
    require_once('lib/codebird.php');
    include("lib/db.php");
    
    \Codebird\Codebird::setConsumerKey($key, $secret);
    $cb = \Codebird\Codebird::getInstance();

    session_start();

    if(!isset($_GET['oauth_verifier'])){

        $reply = $cb->oauth_requestToken(array('oauth_callback' => $url.'1-create_tokens.php'));
        $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
        $_SESSION['oauth_token'] = $reply->oauth_token;
        $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
        $auth_url = $cb->oauth_authorize();
        header('Location: ' . $auth_url);
        die();

    }elseif(isset($_GET['oauth_verifier'])){

        $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
        $reply = $cb->oauth_accessToken(array('oauth_verifier' => $_GET['oauth_verifier']));
        $sql = 'INSERT INTO `'.$db_prefix.'twitter_token` (`token`, `secret`)VALUES("'.$reply->oauth_token.'", "'.$reply->oauth_token_secret.'")';
        $insert = query_mysql($sql, $link);
        mysql_free_result($insert);

    }

?>
<html><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8"></head><body>
<?php

    echo 'Twitter Token Creation completed. <a href="2-create_request.php">Continue</a>';

?>
</body></html>