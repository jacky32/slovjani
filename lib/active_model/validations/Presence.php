<?php

namespace ActiveModel\Validations;

/**
 * Validation exception raised when required attributes are missing.
 */
class PresenceException extends \ActiveModel\ValidationException {};

/**
 * Trait providing presence validation for required model attributes.
 */
trait PresenceValidator
{
  /**
   * Validates that each listed attribute has a non-null value.
   * Usage in model: 'presence' => ['username', 'email']
   *
   * @param array $attributes Indexed array of attribute names to check.
   * @return array Array of violation records, each with 'class', 'attribute', and 'message' keys.
   */
  public function validates_presence_of(array $attributes)
  {
    $caught_exceptions = [];
    foreach ($attributes as $attribute) {
      if ($this->{($attribute)} == null) {
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => $attribute,
          'message' => t("errors.cannot_be_blank")
        ];
      }
    }
    return $caught_exceptions;
  }
}
