<?php

/**
 * File attachment model supporting polymorphic parent resources.
 *
 * @package Models
 */
class Attachment extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'resource_id',
    'resource_type',
    'file_name',
    'file_size',
    'file_type',
    'visible_name',
    'token',
    'creator_id',
    'is_publicly_visible',
    'created_at',
    'updated_at'
  ];

  protected static array $relations  = [
    'belongs_to' => [
      'creator' => [
        'class_name' => User::class,
        'foreign_key' => 'creator_id'
      ]
    ],
    'belongs_to' => [
      'attachable' => [
        'polymorphic' => true,
        'foreign_key' => 'resource_id',
        'foreign_type' => 'resource_type'
      ]
    ]
  ];

  protected static array $validations = [
    'presence' => ['creator_id', 'file_name', 'file_size', 'file_type', 'token', 'resource_id', 'resource_type', 'visible_name'],
  ];

  /**
   * Initialises the Attachment, auto-generating a random hex token when none
   * is supplied in $data.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
    if (!isset($data['token'])) {
      $this->token = bin2hex(random_bytes(16));
    }
  }

  // Methods

}
