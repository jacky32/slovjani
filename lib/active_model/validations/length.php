<?php

namespace ActiveModel\Validations;

trait LengthValidator
{
  // 'length' => [["name" => ["min" => 8, "max" => 255], "description" => ["min" => 8, "max" => 1000]]]
  public function validates_length_of(array $attributes)
  {
    foreach ($attributes as $attribute => $constraints) {
      if (isset($constraints['min']) && $this->{($attribute)}->length() < $constraints['min']) {
        throw new \Exception("{$attribute} must be at least {$constraints['min']} characters long");
      }
      if (isset($constraints['max']) && $this->{($attribute)}->length() > $constraints['max']) {
        throw new \Exception("{$attribute} cannot be longer than {$constraints['max']} characters");
      }
      if (isset($constraints['is']) && $this->{($attribute)}->length() != $constraints['is']) {
        throw new \Exception("{$attribute} must be exactly {$constraints['is']} characters long");
      }
    }
  }
}
