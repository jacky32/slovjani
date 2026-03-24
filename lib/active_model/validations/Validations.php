<?php

namespace ActiveModel;

/**
 * Aggregate validation exception carrying a list of field-level errors.
 */
class ValidationException extends \Exception
{

  private $validation_exceptions;

  /**
   * @param string          $message            Human-readable error summary.
   * @param int             $code               Exception code.
   * @param \Throwable|null $previous           Previous exception in the chain.
   * @param array           $validation_exceptions Array of individual field error arrays.
   */
  public function __construct(String $message = "", int $code = 0, \Throwable|null $previous = null, array $validation_exceptions = [])
  {
    parent::__construct($message, $code, $previous);

    $this->validation_exceptions = $validation_exceptions;
  }

  /**
   * Returns all individual field validation errors collected in this exception.
   *
   * @return array Array of associative arrays with 'class', 'attribute', and 'message' keys.
   */
  public function getValidationExceptions()
  {
    return $this->validation_exceptions;
  }
}


use ActiveModel\Validations\PresenceValidator;
use ActiveModel\Validations\InclusionValidator;
use ActiveModel\Validations\UniquenessValidator;
use ActiveModel\Validations\LengthValidator;

/**
 * Trait composing all built-in validators used by ActiveModel.
 */
trait Validations
{
  use PresenceValidator;
  use InclusionValidator;
  use UniquenessValidator;
  use LengthValidator;
}
