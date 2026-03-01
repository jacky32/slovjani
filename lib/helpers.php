<?php

/**
 * Converts a string to snake_case.
 * Example: "UserProfile" => "user_profile"
 */
function toSnakeCase(string $input): string
{
  return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
}

/**
 * Converts a string to PascalCase.
 * Example: "user_profile" => "UserProfile"
 */
function toPascalCase(string $input): string
{
  return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
}

/**
 * Simple translation function that loads translations from a YAML file.
 * The key is in the format "namespace.key.subkey".
 * Example: t("posts.new.title") would look for "posts" => ["new" => ["title" => "Title"]]
 */
function t(string $key, array $params = []): string
{
  $translations = yaml_parse_file('config/locales/cs.yml');
  $keys = explode('.', $key);
  $value = $translations;
  foreach ($keys as $k) {
    if (isset($value[$k])) {
      $value = $value[$k];
    } else {
      return $key;
    }
  }
  foreach ($params as $param_key => $param_value) {
    $value = str_replace('{' . $param_key . '}', $param_value, $value);
  }
  return $value;
}

function asset_path($path)
{
  $filePath = __DIR__ . '/../public' . $path;
  if (file_exists($filePath)) {
    $mtime = filemtime($filePath);
    return $path . '?v=' . $mtime;
  }
  return $path;
}
