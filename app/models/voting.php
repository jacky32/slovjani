<?php
class Voting extends ApplicationRecord
{
  public $id;
  public $datetime_start;
  public $datetime_end;
  public $name;
  public $status;
  public $description;
  public $creator_id;
  public $created_at;
  public $updated_at;

  public $creator;
  public $questions;

  protected static array $db_attributes = ['id', 'datetime_start', 'datetime_end', 'name', 'status', 'description', 'creator_id', 'created_at', 'updated_at'];

  protected static array $relations  = [
    'belongs_to' => [
      'creator' => [
        'class_name' => User::class,
        'foreign_key' => 'creator_id'
      ]
    ],
    'has_many' => [
      'questions' => [
        'class_name' => Question::class,
        'foreign_key' => 'voting_id'
      ]
    ]
  ];

  protected static array $validations = [
    'presence' => ['datetime_start', 'datetime_end', 'name', 'status', 'description', 'creator_id'],
    'length' => [["name" => ["min" => 8, "max" => 255], "description" => ["min" => 8, "max" => 1000]]],
    'inclusion' => ['status' => ['DRAFT', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED']]
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
