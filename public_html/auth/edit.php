<?php

include_once __DIR__ . '/config.php';

use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Token;

// Configure the OAuth client with the URL and consumer details.
$conf = new ClientConfig($oauthUrl);
$conf->setConsumer(new Consumer($consumerKey, $consumerSecret));
$conf->setUserAgent($gUserAgent);
$client = new Client($conf);

// if (!isset($_SESSION['access_key']) || !isset($_SESSION['access_secret'])) {
//     echo "Access token not found in session.";
//     exit;
// }

// Load the Access Token from the session.
session_start();
$accessToken = new Token($_SESSION['access_key'], $_SESSION['access_secret']);

// Example 1: get the authenticated user's identity.
$ident = $client->identify($accessToken);
echo "You are authenticated as " . htmlspecialchars($ident->username, ENT_QUOTES, 'UTF-8') . ".\n\n";

function get_edit_token()
{
	global $client, $accessToken, $apiUrl, $editToken;
	// Example 3: make an edit (getting the edit token first).
	$response = $client->makeOAuthCall(
		$accessToken,
		"$apiUrl?action=query&meta=tokens&format=json"
	);
	$editToken = json_decode($response)->query->tokens->csrftoken;
	//---
	return $editToken;
}

$apiParams = [
	'action' => 'edit',
	'title' => 'User:' . $ident->username,
	'section' => 'new',
	'summary' => 'Hello World',
	'text' => 'I am learning to use the <code>mediawiki/oauthclient</code> library.',
	'token' => get_edit_token(),
	'format' => 'json',
];
$editResult = json_decode($client->makeOAuthCall(
	$accessToken,
	$apiUrl,
	true,
	$apiParams
));
echo "\n== You made an edit ==\n\n";
print_r($editResult);
