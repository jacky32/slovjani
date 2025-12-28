<?php
require __DIR__ . '/validations/validations.php';

use ActiveModel\Validations;

class ActiveModel
{
  use Validations;

  protected $attributes = [];

  public static function humanAttributeName($attribute)
  {
    return t("attributes." . toSnakeCase(static::class) . "." . $attribute);
  }

  // TODO: implement auto-setting timestamps after create/update

  // public function __call($method, $arguments)
  // {
  //   // Handle set_* methods
  //   // if (strpos($method, 'set_') === 0) {
  //   //   $property = substr($method, 4);
  //   //   $this->attributes[$property] = $arguments[0];
  //   //   return $this;
  //   // }

  //   // Handle get_* methods
  //   // if (strpos($method, 'get_') === 0) {
  //   //   $property = substr($method, 4);
  //   //   return $this->attributes[$property] ?? null;
  //   // }
  //   // if ($this->attributes[$method] ?? false) {
  //   //   return $this->attributes[$method];
  //   // }

  //   throw new Exception("Method $method does not exist");
  // }
}
