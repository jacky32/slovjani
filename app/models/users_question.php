<?php
class Question extends ApplicationRecord
{
  public $id;
  public $question_id;
  public $user_id;
  public $chosen_option;
  public $created_at;
  public $updated_at;

  protected static array $db_attributes = ['id', 'question_id', 'user_id', 'chosen_option', 'created_at', 'updated_at'];

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
    'inclusion' => ['chosen_option' => ['yes', 'no', 'abstain']],
    'uniqueness' => [['user_id', 'question_id']]
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}
