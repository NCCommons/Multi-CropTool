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

function make_callback_url($tool_folder)
{
    global $main_site;
    $test = $_REQUEST['test'] ?? '';
    //---
    $state = ($test != '') ? "&test=$test" : '';
    //---
    $oauth_call = $main_site . "/$tool_folder/auth.php?a=callback" . $state;
    //---
    return $oauth_call;
}

$client->setCallback(make_callback_url($tool_folder));

// Send an HTTP request to the wiki to get the authorization URL and a Request Token.
// These are returned together as two elements in an array (with keys 0 and 1).
list($authUrl, $token) = $client->initiate();

// Store the Request Token in the session. We will retrieve it from there when the user is sent back
// from the wiki (see demo/callback.php).
session_start();
$_SESSION['request_key'] = $token->key;
$_SESSION['request_secret'] = $token->secret;

// Redirect the user to the authorization URL. This is usually done with an HTTP redirect, but we're
// making it a manual link here so you can see everything in action.
echo "Go to this URL to authorize this demo:<br /><a href='$authUrl'>$authUrl</a>";
header("Location: $authUrl");
