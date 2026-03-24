<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Join model storing a user's selected answer for a voting question.
 *
 * @package Models
 */
class UsersQuestion extends ApplicationRecord
{
  protected static array $db_attributes = [
    'question_id',
    'user_id',
    'chosen_option',
    'created_at',
    'updated_at'
  ];

  protected static array $composite_primary_key = ['user_id', 'question_id'];

  protected static array $relations  = [
    'belongs_to' => [
      'question' => [
        'class_name' => Question::class,
        'foreign_key' => 'question_id'
      ],
      'user' => [
        'class_name' => User::class,
        'foreign_key' => 'user_id'
      ]
    ]
  ];

  protected static array $validations = [
    'presence' => ['question_id', 'user_id', 'chosen_option'],
    'inclusion' => ['chosen_option' => ['YES', 'NO', 'ABSTAIN']],
    'uniqueness' => [['user_id', 'question_id']]
  ];

  /**
   * Initialises the UsersQuestion with the provided attribute data.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}

