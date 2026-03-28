<?php

declare(strict_types=1);

/**
 * Applies default security headers for dynamic responses.
 */
function applyDefaultSecurityHeaders(): void
{
  $cspDirectives = [
    "default-src 'self'",
    "base-uri 'self'",
    "form-action 'self'",
    "object-src 'none'",
    "frame-ancestors 'none'",
    "script-src 'self' 'unsafe-inline' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/",
    "style-src 'self' 'unsafe-inline' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/",
    "img-src 'self' data: https://www.gstatic.com/recaptcha/ https://www.google.com/recaptcha/",
    "connect-src 'self' https://www.google.com/recaptcha/",
    "frame-src 'self' https://calendar.google.com https://www.google.com/recaptcha/ https://recaptcha.google.com/recaptcha/",
  ];

  header('Content-Security-Policy: ' . implode('; ', $cspDirectives));
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: DENY');
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Define allowed static file extensions
$allowedExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'otf', 'eot'];

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
      'otf' => 'application/x-font-opentype',
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
    header('Cache-Control: public, max-age=31536000, immutable');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    readfile($realPath);
    exit;
  }
}


// Start the session early so we can check login state before the pregen shortcut.
session_start();
applyDefaultSecurityHeaders();

// Check for pregenerated static HTML before booting the full app.
require_once __DIR__ . '/app/services/StaticPageRouter.php';

// Serve pregenerated static HTML for public pages (GET requests only, guests only)
// Covers /posts, /posts?page=N, /posts/:id, /events, /events?page=N, /events/:id
// Logged-in users are always routed through PHP so they see live, session-aware content.
$userIsLoggedIn  = isset($_SESSION['auth_logged_in']) && $_SESSION['auth_logged_in'] === true;
$pregeneratedFile = (new App\Services\StaticPageRouter(__DIR__ . '/pregenerated'))->resolve(
  $_SERVER['REQUEST_METHOD'],
  $userIsLoggedIn,
  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
  (int) ($_GET['page'] ?? 1)
);

if ($pregeneratedFile !== null) {
  header('Content-Type: text/html; charset=UTF-8');
  header('X-Pregenerated: true');
  readfile($pregeneratedFile);
  exit;
}
// Continue with normal PHP routing for all other requests (including logged-in users and non-GET requests).

$appConfig = require './config/Application.php';
require __DIR__ . '/vendor/autoload.php';

// Load .env variables into environment
$env = file_get_contents(__DIR__ . "/.env");
$lines = explode("\n", $env);

foreach ($lines as $line) {
  preg_match("/([^#]+)\=(.*)/", $line, $matches);
  if (isset($matches[2])) {
    putenv(trim($line));
  }
}

// Uncomment to reset DB schema
// ScriptManager::loadSchema($appConfig['connection'], true);
// Uncomment to load DB and tables without dropping existing DB
ScriptManager::loadSchema($appConfig['connection']);

// router
$router = new Router();

$controllerName = $router->controllerName;
$action = $router->action;

// CSRF token
if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

$db = new App\Services\Database();
$dbConnection = null;
if ($db) {
  $dbConnection = $db->getConnection();
}

Logger::info("Started " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI'] . " for " . $_SERVER['REMOTE_ADDR'] . " at " . date('Y-m-d H:i:s'));
Logger::info("Processing " . $controllerName . "#" . $action);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $filtered = array_map(function ($key) {
    return in_array($key, ['password', 'token']) ? '[FILTERED]' : $_POST[$key];
  }, array_keys($_POST));
  Logger::info("POST params: ", $filtered);
}
if ($_FILES) {
  Logger::info("Uploaded files: ", $_FILES);
}

if (!class_exists($controllerName, true) && strpos($controllerName, '\\') === false) {
  $namespacedController = 'App\\Controllers\\' . $controllerName;
  if (class_exists($namespacedController, true)) {
    $controllerName = $namespacedController;
  }
}

$controller = new $controllerName($dbConnection);
$request = $_REQUEST;
$request['page'] ??= NULL;
$controller->{$action}($request);
