<?php

namespace ActiveModel;

require __DIR__ . '/presence.php';

use ActiveModel\Validations\PresenceValidator;

trait Validations
{
  use PresenceValidator;
}
