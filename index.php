<?php
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
