<?php

namespace ActiveModel\Validations;

class PresenceException extends \ActiveModel\ValidationException {};

trait PresenceValidator
{
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
