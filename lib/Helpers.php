<?php

/**
 * Converts a string to snake_case.
 * Example: "UserProfile" => "user_profile"
 *
 * @param string $input The string to convert.
 * @return string The snake_case representation.
 */
if (!function_exists('toSnakeCase')) {
  function toSnakeCase(string $input): string
  {
    if (str_contains($input, '\\')) {
      $input = (string) preg_replace('/^.*\\\\/', '', $input);
    }

    return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
  }
}

/**
 * Converts a string to PascalCase.
 * Example: "user_profile" => "UserProfile"
 *
 * @param string $input The snake_case string to convert.
 * @return string The PascalCase representation.
 */
if (!function_exists('toPascalCase')) {
  function toPascalCase(string $input): string
  {
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
  }
}

/**
 * Simple translation function that loads translations from a YAML file.
 * The key is in the format "namespace.key.subkey".
 * Example: t("posts.new.title") would look for "posts" => ["new" => ["title" => "Title"]]
 *
 * @param string $key    Dot-separated translation key.
 * @param array  $params Optional named placeholders to interpolate into the translation string.
 * @return string The translated string, or $key if not found.
 */
if (!function_exists('t')) {
  function t(string $key, array $params = []): string
  {
    static $translations = null;

    if ($translations === null) {
      $localePath = __DIR__ . '/../config/locales/cs.yml';
      if (!is_readable($localePath)) {
        $translations = [];
      } else {
        $parsed = yaml_parse_file($localePath);
        $translations = is_array($parsed) ? $parsed : [];
      }
    }

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
}

/**
 * Returns a cache-busted public asset path by appending the file's mtime as a
 * query-string version parameter.
 *
 * @param string $path Asset path relative to the public/ directory (e.g. '/assets/app.css').
 * @return string The path with an appended ?v=<mtime> query string, or $path if the file does not exist.
 */
if (!function_exists('asset_path')) {
  function asset_path($path)
  {
    $filePath = __DIR__ . '/../public' . $path;
    if (file_exists($filePath)) {
      $mtime = filemtime($filePath);
      return $path . '?v=' . $mtime;
    }
    return $path;
  }
}
