<?php
class Post extends ApplicationRecord
{
  public $id;
  public $creator_id;
  public $name;
  public $body;
  public $author;
  public $created_at;

  protected static array $db_attributes = ['id', 'name', 'body', 'creator_id'];

  protected static array $relations  = [
    'belongs_to' => [
      'author' => [
        'class_name' => User::class,
        'foreign_key' => 'creator_id'
      ]
    ]
  ];

  protected static array $validations = [
    "presence" => ["name", "body", "creator_id"]
    //   // if (strlen($this->get_body()) > 255) throw new Exception("Body cannot be longer than 255 characters");
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
