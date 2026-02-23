<?php

/**
 * @package Config
 */

return [
  'connection' => [
    'database_type' => 'mysqli',
    'host' => getenv('MYSQL_HOST') ?: 'localhost:3307',
    'port' => getenv('MYSQL_PORT') ?: '3307',
    'user' => getenv('MYSQL_USER') ?: 'root',
    'password' => getenv('MYSQL_PASSWORD') ?: 'abcd',
    'dbname' => getenv('MYSQL_DATABASE') ?: 'php_app_development'
  ]
];
