<?php

	$twitter_username = "USERNAME";

	//--------------
	//You need to create an app key on developer.twitter.com
	//In the process you will be asked to provide a callback URL
	//This url needs to point to the URL where this script is stored
	//This doesn't work when you are trying to run this locally
	//You need to have a webserver running this script
	//--------------

	$key = "APP-KEY";
	$secret = "APP-SECRET";
	$url = "TWITTER-CALLBACK-URL";

	//--------------
	//This array holds the requests you want to setup
	//
	//The key needs to be unique as it is going to be 
	//used as an identifier in the database
	//
	//Every request holds an array of parameters
	//For in detail description of how the search_tweets function works
	//take a look at https://github.com/jublonet/codebird-php
	//
	//The parameters follow the concept of the API
	//https://dev.twitter.com/docs/api/1/get/search
	//--------------

	$requests = array(
		"KEY" => array(
			'count'=>'100' //This parameter is mandatory
			'q' => '#HASHTAG'
		)
	);

	$db_server = "SERVER";
	$db_user = "USERNAME";
	$db_pass = "PASSWORD";
	$db_database = "DATABASE";
	$db_prefix = "";

	header('Content-type: text/plain;charset=utf8');

?>