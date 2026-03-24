<?php

namespace ActiveModel\Validations;

/**
 * Validation exception raised for inclusion constraint violations.
 */
class InclusionException extends \ActiveModel\ValidationException {};

/**
 * Trait providing inclusion validation for model attributes.
 */
trait InclusionValidator
{
  /**
   * Validates that each attribute's value is within its list of allowed values.
   * Usage in model: 'inclusion' => ["status" => ["active", "archived", "pending"]]
   *
   * @param array $attributes Associative array of attribute names to arrays of allowed values.
   * @return array Array of violation records, each with 'class', 'attribute', and 'message' keys.
   */
  public function validates_inclusion_of(array $attributes)
  {
    $caught_exceptions = [];
    foreach ($attributes as $attribute => $allowed_values) {
      if (!in_array($this->{$attribute}, $allowed_values)) {
        // throw new InclusionException("{$this->{$attribute}} is not included in possible options for field {$attribute}.");
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => $attribute,
          'message' => t("errors.must_be_included_in", ['options' => implode(", ", $allowed_values)])
        ];
      }
    }
    return $caught_exceptions;
  }
}
