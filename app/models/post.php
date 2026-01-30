<?php
class Post extends ApplicationRecord
{
  public $id;
  public $name;
  public $body;
  public $creator_id;
  public $creator;
  public $created_at;
  public $updated_at;

  protected static array $db_attributes = ['id', 'name', 'body', 'creator_id', 'created_at', 'updated_at'];

  protected static array $relations  = [
    'belongs_to' => [
      'creator' => [
        'class_name' => User::class,
        'foreign_key' => 'creator_id'
      ]
    ]
  ];

  protected static array $validations = [
    "presence" => ["name", "body", "creator_id"],
    "length" => ["name" => ["min" => 5, "max" => 100], "body" => ["min" => 10, "max" => 5000]]
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
