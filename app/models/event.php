<?php
/**
 * @package Models
 */
class Event extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'creator_id',
    'name',
    'description',
    'datetime_start',
    'datetime_end',
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
    'has_many' => [
      'attachments' => [
        'class_name' => Attachment::class,
        'foreign_key' => 'resource_id',
        'foreign_type' => 'resource_type',
        'polymorphic' => true
      ]
    ]
  ];

  protected static array $validations = [
    'presence' => ['creator_id', 'name', 'description', 'datetime_start'],
    'length' => ["name" => ["min" => 4, "max" => 255], "description" => ["min" => 4, "max" => 5000]]
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
