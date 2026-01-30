<?php

namespace ActiveModel\Validations;

class PresenceException extends \ActiveModel\ValidationException {};

trait PresenceValidator
{
  public function validates_presence_of(array $attributes)
  {
    foreach ($attributes as $attribute) {
      if ($this->{($attribute)} == null) {
        throw new PresenceException("{$attribute} cannot be empty");
      }
    }
  }
}
