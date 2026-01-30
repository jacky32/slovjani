<?php

namespace ActiveModel;

class ValidationException extends \Exception {};

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
