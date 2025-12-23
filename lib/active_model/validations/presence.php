<?php

namespace ActiveModel\Validations;

trait PresenceValidator
{
  public function validates_presence_of(array $attributes)
  {
    foreach ($attributes as $attribute) {
      if ($this->{($attribute)} == null) {
        throw new \Exception("{$attribute} cannot be empty");
      }
    }
  }
}
