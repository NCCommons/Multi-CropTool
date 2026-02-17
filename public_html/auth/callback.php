<?php

include_once __DIR__ . '/config.php';

use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Token;

if (!isset($_GET['oauth_verifier'])) {
	echo "This page should only be access after redirection back from the wiki.";
	echo <<<HTML
		Go to this URL to authorize this tool:<br />
		<a href='auth.php?a=login'>Login</a><br />
	HTML;
	exit(1);
}

// Configure the OAuth client with the URL and consumer details.
$conf = new ClientConfig($oauthUrl);
$conf->setConsumer(new Consumer($consumerKey, $consumerSecret));
$conf->setUserAgent($gUserAgent);
$client = new Client($conf);

// Get the Request Token's details from the session and create a new Token object.
session_start();
$requestToken = new Token(
	$_SESSION['request_key'],
	$_SESSION['request_secret']
);

// Send an HTTP request to the wiki to retrieve an Access Token.
$accessToken1 = $client->complete($requestToken, $_GET['oauth_verifier']);

// At this point, the user is authenticated, and the access token can be used to make authenticated
// API requests to the wiki. You can store the Access Token in the session or other secure
// user-specific storage and re-use it for future requests.
$_SESSION['access_key'] = $accessToken1->key;
$_SESSION['access_secret'] = $accessToken1->secret;

// You also no longer need the Request Token.
unset($_SESSION['request_key'], $_SESSION['request_secret']);

include_once __DIR__ . '/user_infos.php';
// The demo continues in demo/edit.php
echo "Continue to <a href='auth.php?a=edit'>edit</a><br>";
echo "Continue to <a href='auth.php?a=index'>index</a><br>";

$username = get_user_name();
$_SESSION['username'] = $username;
// Example 3: make an edit (getting the edit token first).
# automatic redirect to edit.php

$test = $_REQUEST['test'] ?? '';
$state = ($test != '') ? "?test=$test" : "";
//---
$newurl = "/$tool_folder/index.php$state";
//---
echo "header('Location: $newurl');<br>";
//---
header("Location: $newurl");
//---
