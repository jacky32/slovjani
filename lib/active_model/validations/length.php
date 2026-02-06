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
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => $attribute,
          'message' => t("errors.must_be_longer_than", ['count' => $constraints['min']])
        ];
      }
      if (isset($constraints['max']) && strlen($this->{($attribute)}) > $constraints['max']) {
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => $attribute,
          'message' => t("errors.must_not_be_longer_than", ['count' => $constraints['max']])
        ];
      }
      if (isset($constraints['is']) && strlen($this->{($attribute)}) != $constraints['is']) {
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
