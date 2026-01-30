<?php

namespace ActiveModel\Validations;

class LengthException extends \ActiveModel\ValidationException {};

trait LengthValidator
{
  // 'length' => ["name" => ["min" => 8, "max" => 255], "description" => ["min" => 8, "max" => 1000]]
  public function validates_length_of(array $attributes)
  {
    foreach ($attributes as $attribute => $constraints) {
      if (isset($constraints['min']) && strlen($this->{($attribute)}) < $constraints['min']) {
        throw new LengthException("{$attribute} must be at least {$constraints['min']} characters long");
      }
      if (isset($constraints['max']) && strlen($this->{($attribute)}) > $constraints['max']) {
        throw new LengthException("{$attribute} cannot be longer than {$constraints['max']} characters");
      }
      if (isset($constraints['is']) && strlen($this->{($attribute)}) != $constraints['is']) {
        throw new LengthException("{$attribute} must be exactly {$constraints['is']} characters long");
      }
    }
  }
}
