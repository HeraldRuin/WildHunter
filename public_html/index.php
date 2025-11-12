<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// PATH TO CMS
const CMS_PATH = 'bc-cms';

if (!empty($_SERVER['REQUEST_URI'])) {
    if (strpos($_SERVER['REQUEST_URI'], '/install') !== false) {
        if (!file_exists(__DIR__ . '/../' . CMS_PATH . '/.env')) {
            copy(__DIR__ . '/../' . CMS_PATH . '/.env.example', __DIR__ . '/../' . CMS_PATH . '/.env');
        }
    }
}
if (!version_compare(phpversion(), '8.2', '>')) {
    die("Current PHP version: " . phpversion() . "<br>You must upgrade PHP version 8.2 or later");
}
if (file_exists(__DIR__ . '/../' . CMS_PATH . '/storage/bc.php')) {
    require __DIR__ . '/../' . CMS_PATH . '/storage/bc.php';
}
if (file_exists(__DIR__ . '/../' . CMS_PATH . '/storage/pro.php')) {
    require __DIR__ . '/../' . CMS_PATH . '/storage/pro.php';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../' . CMS_PATH . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../' . CMS_PATH . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__ . '/../' . CMS_PATH . '/bootstrap/app.php';

// set the public path to this directory
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());