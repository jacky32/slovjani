<?php
function toSnakeCase($input)
{
  return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
}

function toPascalCase($input)
{
  return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
}

function t($key)
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
