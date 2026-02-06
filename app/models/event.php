<?php
class Event extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'creator_id',
    'name',
    'description',
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
  ];

  protected static array $validations = [
    'presence' => ['creator_id', 'name', 'description'],
    'length' => ["name" => ["min" => 4, "max" => 255], "description" => ["min" => 4, "max" => 5000]]
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
