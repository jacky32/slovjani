<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Comment model for polymorphic threaded comments across resources.
 *
 * @package Models
 */
class Comment extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'resource_id',
    'resource_type',
    'body',
    'parent_comment_id',
    'creator_id',
    'created_at',
    'updated_at'
  ];

  protected static array $relations  = [
    'belongs_to' => [
      'creator' => [
        'class_name' => User::class,
        'foreign_key' => 'creator_id'
      ],
      'commentable' => [
        'polymorphic' => true,
        'foreign_key' => 'resource_id',
        'foreign_type' => 'resource_type'
      ],
      'parent_comment' => [
        'class_name' => self::class,
        'foreign_key' => 'parent_comment_id'
      ]
    ]
  ];

  protected static array $validations = [
    'presence' => ['creator_id', 'body', 'resource_id', 'resource_type'],
  ];

  /**
   * Initialises the Comment with the provided attribute data.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}

class_alias(__NAMESPACE__ . '\\Comment', 'Comment');
