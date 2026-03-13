<?php

/**
 * Question model belonging to a voting with user responses.
 *
 * @package Models
 */
class Question extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'voting_id',
    'name',
    'description',
    'created_at',
    'updated_at'
  ];

  protected static array $relations  = [
    'belongs_to' => [
      'voting' => [
        'class_name' => Voting::class,
        'foreign_key' => 'voting_id'
      ]
    ],
    'has_many' => [
      'users_questions' => [
        'class_name' => UsersQuestion::class,
        'foreign_key' => 'question_id'
      ]
    ]
  ];

  protected static array $validations = [
    'presence' => ['voting_id', 'name', 'description'],
    'length' => ["name" => ["min" => 4, "max" => 255], "description" => ["min" => 4, "max" => 1000]]
  ];

  /**
   * Initialises the Question with the provided attribute data.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
