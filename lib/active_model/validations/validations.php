<?php

namespace ActiveModel;

require __DIR__ . '/presence.php';

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
