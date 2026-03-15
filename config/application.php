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
  ],
  'google_calendar' => [
    'public_calendar_id' => getenv('GOOGLE_PUBLIC_CALENDAR_ID') ?: '',
    'private_calendar_id' => getenv('GOOGLE_PRIVATE_CALENDAR_ID') ?: '',
    'service_account_email' => getenv('GOOGLE_SERVICE_ACCOUNT_EMAIL') ?: '',
    'private_key' => str_replace('\\n', PHP_EOL, getenv('GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY') ?: ''),
    'delegated_user' => getenv('GOOGLE_CALENDAR_DELEGATED_USER') ?: null,
    'token_uri' => getenv('GOOGLE_OAUTH_TOKEN_URI') ?: 'https://oauth2.googleapis.com/token',
    'base_uri' => getenv('GOOGLE_CALENDAR_API_BASE_URI') ?: 'https://www.googleapis.com/calendar/v3',
    'timeout_seconds' => (int) (getenv('GOOGLE_API_TIMEOUT_SECONDS') ?: 15),
    'time_zone' => getenv('GOOGLE_CALENDAR_TIMEZONE') ?: date_default_timezone_get(),
  ],
  'recaptcha' => [
    'enabled' => filter_var(getenv('RECAPTCHA_ENABLED') ?: 'false', FILTER_VALIDATE_BOOL),
    'v3_site_key' => getenv('RECAPTCHA_V3_SITE_KEY') ?: '',
    'v3_secret_key' => getenv('RECAPTCHA_V3_SECRET_KEY') ?: '',
    'v3_score_threshold' => (float) (getenv('RECAPTCHA_V3_SCORE_THRESHOLD') ?: 0.5),
    'v3_action' => getenv('RECAPTCHA_V3_ACTION') ?: 'login',
    'v2_site_key' => getenv('RECAPTCHA_V2_SITE_KEY') ?: '',
    'v2_secret_key' => getenv('RECAPTCHA_V2_SECRET_KEY') ?: '',
  ],
];
