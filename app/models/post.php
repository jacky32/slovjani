<?php
class Post extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'name',
    'body',
    'creator_id',
    'status',
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
    "presence" => ["name", "body", "creator_id", "status"],
    "length" => ["name" => ["min" => 5, "max" => 100], "body" => ["min" => 10, "max" => 5000]],
    "inclusion" => ["status" => ["DRAFT", "PUBLISHED", "ARCHIVED"]]
  ];

  public static function publiclyVisible()
  {
    return self::where(['status' => 'PUBLISHED']);
  }

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
