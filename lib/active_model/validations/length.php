<?php

namespace ActiveModel\Validations;

class LengthException extends \ActiveModel\ValidationException {};

trait LengthValidator
{
  // 'length' => ["name" => ["min" => 8, "max" => 255], "description" => ["min" => 8, "max" => 1000]]
  public function validates_length_of(array $attributes)
  {
    $caught_exceptions = [];
    foreach ($attributes as $attribute => $constraints) {
      if (isset($constraints['min']) && strlen($this->{($attribute)}) < $constraints['min']) {
        // throw new LengthException("{$attribute} must be at least {$constraints['min']} characters long");
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => $attribute,
          'message' => t("errors.must_be_at_least_characters_long", ['count' => $constraints['min']])
        ];
      }
      if (isset($constraints['max']) && strlen($this->{($attribute)}) > $constraints['max']) {
        // throw new LengthException("{$attribute} cannot be longer than {$constraints['max']} characters");
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => $attribute,
          'message' => t("errors.must_be_longer_than_characters", ['count' => $constraints['max']])
        ];
      }
      if (isset($constraints['is']) && strlen($this->{($attribute)}) != $constraints['is']) {
        // throw new LengthException("{$attribute} must be exactly {$constraints['is']} characters long");
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => $attribute,
          'message' => t("errors.must_be_exactly_characters_long", ['count' => $constraints['is']])
        ];
      }
    }
    return $caught_exceptions;
  }
}
