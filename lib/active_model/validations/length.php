<?php

namespace ActiveModel\Validations;

/**
 * Validation exception raised for attribute length violations.
 */
class LengthException extends \ActiveModel\ValidationException {};

/**
 * Trait providing min/max/exact-length validation for attributes.
 */
trait LengthValidator
{
  /**
   * Validates min, max, and exact length constraints for each specified attribute.
   * Usage in model: 'length' => ["username" => ['min' => 3, 'max' => 20], "code" => ['is' => 6]]
   *
   * @param array $attributes Associative array mapping attribute names to constraint arrays
   *                          with optional 'min', 'max', and 'is' keys.
   * @return array Array of violation records, each with 'class', 'attribute', and 'message' keys.
   */
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
