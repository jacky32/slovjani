<?php

namespace ActiveModel;

class ValidationException extends \Exception
{

  private $validation_exceptions;

  public function __construct(String $message = "", int $code = 0, \Throwable|null $previous = null, array $validation_exceptions = [])
  {
    parent::__construct($message, $code, $previous);

    $this->validation_exceptions = $validation_exceptions;
  }

  public function getValidationExceptions()
  {
    return $this->validation_exceptions;
  }
}

require __DIR__ . '/presence.php';
require __DIR__ . '/inclusion.php';
require __DIR__ . '/uniqueness.php';
require __DIR__ . '/length.php';

use ActiveModel\Validations\PresenceValidator;
use ActiveModel\Validations\InclusionValidator;
use ActiveModel\Validations\UniquenessValidator;
use ActiveModel\Validations\LengthValidator;

trait Validations
{
  use PresenceValidator;
  use InclusionValidator;
  use UniquenessValidator;
  use LengthValidator;
}
