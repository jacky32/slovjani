<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Define allowed static file extensions
$allowedExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];

// Check if file exists in public directory
$filePath = __DIR__ . '/public' . $uri;
$publicUri = '/public' . $uri;

// Prevent path traversal attacks
$realPath = realpath($filePath);
$publicDir = realpath(__DIR__ . '/public');
if ($realPath && $publicDir && strpos($realPath, $publicDir) === 0 && is_file($realPath)) {
  $extension = pathinfo($realPath, PATHINFO_EXTENSION);
  if (in_array(strtolower($extension), $allowedExtensions)) {
    // Manually serve the static file from /public/
    $mimeTypes = [
      'css' => 'text/css',
      'js' => 'application/javascript',
      'png' => 'image/png',
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'gif' => 'image/gif',
      'svg' => 'image/svg+xml',
      'ico' => 'image/x-icon',
      'woff' => 'font/woff',
      'woff2' => 'font/woff2',
      'ttf' => 'font/ttf',
      'eot' => 'application/vnd.ms-fontobject'
    ];
    $mimeType = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';

    // Add security headers for SVG files
    if ($extension === 'svg') {
      header('Content-Security-Policy: default-src \'none\'; style-src \'unsafe-inline\'');
      header('X-Content-Type-Options: nosniff');
    }

    header('Content-Type: ' . $mimeType);
    header('X-Frame-Options: DENY');
    readfile($realPath);
    exit;
  }
}

$appConfig = require './config/application.php';
require 'lib/helpers.php';
require 'lib/active_model/active_model.php';
require __DIR__ . '/vendor/autoload.php';

// Autoloader
spl_autoload_register(function ($class) {
  if (file_exists('app/controllers/' . toSnakeCase($class) . '.php')) include 'app/controllers/' . toSnakeCase($class) . '.php';
  if (file_exists('app/models/' . toSnakeCase($class) . '.php')) include 'app/models/' . toSnakeCase($class) . '.php';
  if (file_exists('app/services/' . toSnakeCase($class) . '.php')) include 'app/services/' . toSnakeCase($class) . '.php';
  if (file_exists('db/' . toSnakeCase($class) . '.php')) include 'db/' . toSnakeCase($class) . '.php';
  if (file_exists('config/' . toSnakeCase($class) . '.php')) include 'config/' . toSnakeCase($class) . '.php';
});

// Uncomment to reset DB schema
// ScriptManager::loadSchema($appConfig['connection'], true);
// Uncomment to load DB and tables without dropping existing DB
ScriptManager::loadSchema($appConfig['connection']);

// router
$router = new Router();

$controllerName = $router->controllerName;
$action = $router->action;

// CSRF token
session_start();
if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

$db = new Database($appConfig);
$dbConnection = null;
if ($db) {
  $dbConnection = $db->getConnection();
}

$controller = new $controllerName($dbConnection);
$controller->{$action}($_REQUEST);
