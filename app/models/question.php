<?php
class Question extends ApplicationRecord
{
  public $id;
  public $voting_id;
  public $name;
  public $description;
  public $created_at;
  public $updated_at;

  public $voting;

  protected static array $db_attributes = ['id', 'voting_id', 'name', 'description', 'created_at', 'updated_at'];

  protected static array $relations  = [
    'belongs_to' => [
      'voting' => [
        'class_name' => Voting::class,
        'foreign_key' => 'voting_id'
      ]
    ]
  ];

  protected static array $validations = [
    'presence' => ['voting_id', 'name', 'description']
    //   // if (strlen($this->get_body()) > 255) throw new Exception("Body cannot be longer than 255 characters");
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
