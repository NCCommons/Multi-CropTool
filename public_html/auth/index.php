<?php
include_once __DIR__ . '/config.php';

use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;

// Configure the OAuth client with the URL and consumer details.
$conf = new ClientConfig($oauthUrl);
$conf->setConsumer(new Consumer($consumerKey, $consumerSecret));
$conf->setUserAgent($gUserAgent);
$client = new Client($conf);

// Get the Request Token's details from the session and create a new Token object.
session_start();
//---
$username = $_SESSION['username'] ?? '';
//---
