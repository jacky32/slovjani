<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Base model class shared by all application domain models.
 *
 * @package Models
 */
abstract class ApplicationRecord extends \ActiveModel
{
  /**
   * Passes initialisation data up to ActiveModel.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data);
  }
}

