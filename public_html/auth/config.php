<?php
//---
include_once __DIR__ . '/../vendor/autoload.php';
//---
// $tool_folder = '';
// $main_site = '';
// $source_site = '';
// $target_domain = '';
// $inifile = '';
// $gUserAgent = '';
//---
$oauthUrl = 'https://' . $target_domain . '/w/index.php?title=Special:OAuth';
//---
$ini = parse_ini_file($inifile);
//---
if ($ini === false) {
    header("HTTP/1.1 500 Internal Server Error");
    error_log("Failed to read ini file: $inifile");
    echo "Server configuration error. Please contact the administrator.";
    exit(0);
}
if (
    !isset($ini['consumerKey']) ||
    !isset($ini['consumerSecret'])
) {
    header("HTTP/1.1 500 Internal Server Error");
    echo 'Required configuration directives not found in ini file';
    exit(0);
}

// Make the api.php URL from the OAuth URL.
$apiUrl = preg_replace('/index\.php.*/', 'api.php', $oauthUrl);

// When you register, you will get a consumer key and secret. Put these here (and for real
// applications, keep the secret secret! The key is public knowledge.).
$consumerKey    = $ini['consumerKey'];
$consumerSecret =  $ini['consumerSecret'];
