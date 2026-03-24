<?php

declare(strict_types=1);

namespace App\Models;

/**
 * User account model with role masks and owned resources.
 *
 * @package Models
 */
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
    'roles_mask',
    'created_at',
    'updated_at'
  ];

  protected static array $relations  = [
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
      ],
      'comments' => [
        'class_name' => Comment::class,
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

  /**
   * Initialises the User with the provided attribute data.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }
}

