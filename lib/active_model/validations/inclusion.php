<?php

namespace ActiveModel\Validations;

class InclusionException extends \ActiveModel\ValidationException {};

trait InclusionValidator
{
  // 'inclusion' => ['chosen_option' => ['yes', 'no', 'abstain']]
  public function validates_inclusion_of(array $attributes)
  {
    foreach ($attributes as $attribute => $allowed_values) {
      if (!in_array($this->{($attribute)}, $allowed_values)) {
        throw new InclusionException("{$attribute} is not included in possible options for this field.");
      }
    }
  }
}
