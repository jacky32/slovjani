<?php
class User extends ApplicationRecord
{
  const AVAILABLE_ROLES = [
    'admin' => \Delight\Auth\Role::ADMIN,
    'collaborator' => \Delight\Auth\Role::COLLABORATOR,
    'none' => 0
  ];

  protected static array $db_attributes = [
    'id',
    'username',
    'email',
    'password',
    'roles_mask'
  ];

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
      ],
      'attachments' => [
        'class_name' => Attachment::class,
        'foreign_key' => 'resource_id',
        'foreign_type' => 'resource_type',
        'polymorphic' => true
      ]
    ]
  ];

  protected static array $validations = [
    "presence" => ["username", "email", "password"],
    "length" => [
      "username" => ["min" => 3, "max" => 16],
      // "password" => ["min" => 8, "max" => 64]
    ],
    "uniqueness" => ["username", "email"],
    "inclusion" => [
      "role" => [1, 4, 0]
    ]
  ];

  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }
}
