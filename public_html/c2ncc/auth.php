<?php
//---
if (isset($_REQUEST['test']) || $_SERVER['SERVER_NAME'] == 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};
//---
require_once __DIR__ . "/text.php";
//---
$ROOT_PATH = explode('public_html', __FILE__)[0];
//---
$tool_folder = "c2ncc";
//---
$main_site = "https://nccroptool.toolforge.org";
//---
$source_site = "commons.wikimedia.org";
$target_domain = "nccommons.org";
//---
$inifile = $ROOT_PATH . '/confs/OAuthConfig.ini';
//---
$gUserAgent = 'xcommons_c2ncc MediaWikiOAuthClient/1.0';
//---
require_once __DIR__ . "/../auth/load.php";
