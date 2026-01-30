<?php

namespace ActiveModel\Validations;

trait InclusionValidator
{
  // 'inclusion' => ['chosen_option' => ['yes', 'no', 'abstain']]
  public function validates_inclusion_of(array $attributes)
  {
    foreach ($attributes as $attribute => $allowed_values) {
      if (!in_array($this->{($attribute)}, $allowed_values)) {
        throw new \Exception("{$attribute} is not included in possible options for this field.");
      }
    }
  }
}
