<?php

namespace ActiveModel\Validations;

class InclusionException extends \ActiveModel\ValidationException {};

trait InclusionValidator
{
  // 'inclusion' => ['chosen_option' => ['yes', 'no', 'abstain']]
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
