<?php
class User extends ApplicationRecord
{
  public $id;
  public $username;
  public $email;
  public $password;


  public $posts;
  public $votings;
  public $users_questions;

  protected static array $db_attributes = ['id', 'username', 'email', 'password'];

  protected static array $relations  = [
    // 'belongs_to' => [
    //   'creator' => [
    //     'class_name' => User::class,
    //     'foreign_key' => 'creator_id'
    //   ]
    // ],
    'has_many' => [
      'posts' => [
        'class_name' => Post::class,
        'foreign_key' => 'creator_id'
      ],
      'votings' => [
        'class_name' => Voting::class,
        'foreign_key' => 'creator_id'
      ],
      'users_questions' => [
        'class_name' => UsersQuestion::class,
        'foreign_key' => 'user_id'
      ]
    ]
  ];

  protected static array $validations = [
    "presence" => ["username", "email", "password"],
    "length" => [
      "username" => ["min" => 3, "max" => 16],
      "password" => ["min" => 8, "max" => 64]
    ],
    "uniqueness" => ["username", "email"],
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }
}
