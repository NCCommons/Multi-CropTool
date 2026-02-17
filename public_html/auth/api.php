<?php

header('Content-type: application/json; charset=utf-8');

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

// Load the Access Token from the session.
session_start();
$accessToken = new Token($_SESSION['access_key'], $_SESSION['access_secret']);

// Example 1: get the authenticated user's identity.
$ident = $client->identify($accessToken);

function get_edit_token()
{
    global $client, $accessToken, $apiUrl;
    // Example 3: make an edit (getting the edit token first).
    $response = $client->makeOAuthCall(
        $accessToken,
        "$apiUrl?action=query&meta=tokens&format=json"
    );
    $editToken = json_decode($response)->query->tokens->csrftoken;
    //---
    return $editToken;
}

function doApiQuery($Params, $addtoken = null)
{
    global $client, $accessToken, $apiUrl;
    //---
    if ($addtoken !== null) $Params['token'] = get_edit_token();
    //---
    $Result = $client->makeOAuthCall(
        $accessToken,
        $apiUrl,
        true,
        $Params
    );
    //---
    return json_decode($Result, true);
}

function doEdit($data)
{
    return doApiQuery($data, $addtoken = true);
}

function downloadFile($url)
{
    // Initialize cURL session
    $ch = curl_init($url);

    // Create a temporary file to save the downloaded content
    $tmp_file = tempnam(sys_get_temp_dir(), 'downloaded_file_');

    // Set options for cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For HTTPS

    // Execute cURL request and save the downloaded content to the temporary file
    $file_data = curl_exec($ch);
    file_put_contents($tmp_file, $file_data);

    // Close cURL session
    curl_close($ch);

    return $tmp_file;
}

function upload($post)
{
    $url = $post['url'] ?? '';
    $file = $_FILES['file'];

    // Validate and sanitize other inputs if needed
    $filename = filter_var($post['filename'] ?? '', FILTER_SANITIZE_STRING);
    $comment = filter_var($post['comment'] ?? '', FILTER_SANITIZE_STRING);

    $data = [
        'action' => 'upload',
        'format' => 'json',
        'text' => "",
        'filename' => $filename,
        'comment' => $comment,
    ];

    if ($url == '' && (!isset($file))) {
        $err = ["error" => "Invalid", "filename" => $filename, "url" => $url];
        echo json_encode($err);
        return;
    }

    // Download the file using the separate function
    $by = $post['by'] ?? 'file';

    if ($by == 'url') {
        $data['url'] = $url;
    } else {
        // CURLFile
        if (isset($file)) {
            $data['file'] = new \CURLFile($file['name'], $file['type'], $file['tmp_name'], $file['size']);
        } else {
            $tmp_file = downloadFile($url);
            $data['file'] = new \CURLFile($tmp_file);
        }
    }
    // Perform whatever processing or API call you need with the uploaded file
    $response = doEdit($data);

    // Delete the temporary file after processing
    unlink($tmp_file);

    // Output the response
    echo json_encode($response);
}

function find_exists()
{
    // Sanitize the filename to prevent malicious code injection
    $sanitizedFilename = filter_var($_GET['filename'], FILTER_SANITIZE_STRING);
    $filename = $sanitizedFilename ?? '';

    $params = [
        'action' => 'query',
        'format' => 'json',
        'formatversion' => '2',
        'titles' => "File:" . $filename
    ];

    //---
    $res = doEdit($params);
    //---
    // Check if $res contains the specific phrase "authorized this application yet"
    if (is_string($res) && $res == 'login') {
        // echo "login";
        return;
    }
    //---
    // { "batchcomplete": true, "query": { "normalized": [ { "fromencoded": false, "from": "File:IMG_20220107_153333.jpg", "to": "File:IMG 20220107 153333.jpg" } ], "pages": [ { "pageid": 1190645, "ns": 6, "title": "File:IMG 20220107 153333.jpg" } ] } }
    // echo json_encode($res);
    $pages = $res['query']['pages'][0] ?? null;
    //---
    $result = ["exists" => "false"];
    //---
    if ($pages && !isset($pages['missing'])) {
        $result['exists'] = "true";
    };
    //---
    echo json_encode($result);
}


switch ($_REQUEST['do'] ?? '') {
    case 'upload':
        upload($_REQUEST);
        break;

    case 'exists':
        find_exists();
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
        break;
}
