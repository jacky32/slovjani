<?php
class Voting extends ApplicationRecord
{
  public $id;
  public $datetime_start;
  public $datetime_end;
  public $name;
  public $description;
  public $creator_id;
  public $creator;
  public $created_at;
  public $updated_at;

  protected static array $db_attributes = ['id', 'datetime_start', 'datetime_end', 'name', 'description', 'creator_id', 'created_at', 'updated_at'];

  protected static array $relations  = [
    'belongs_to' => [
      'creator' => [
        'class_name' => User::class,
        'foreign_key' => 'creator_id'
      ]
    ]
  ];

  protected static array $validations = [
    'presence' => ['datetime_start', 'datetime_end', 'name', 'description', 'creator_id']
    //   // if (strlen($this->get_body()) > 255) throw new Exception("Body cannot be longer than 255 characters");
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
