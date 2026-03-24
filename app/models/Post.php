<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Post domain model with publish-state workflow.
 *
 * @package Models
 */
class Post extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'name',
    'body',
    'creator_id',
    'status',
    'created_at',
    'updated_at'
  ];
  protected static array $relations  = [
    'belongs_to' => [
      'creator' => [
        'class_name' => User::class,
        'foreign_key' => 'creator_id'
      ]
    ],
    'has_many' => [
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
    "presence" => ["name", "body", "creator_id", "status"],
    "length" => ["name" => ["min" => 5, "max" => 100], "body" => ["min" => 10, "max" => 5000]],
    "inclusion" => ["status" => ["DRAFT", "PUBLISHED", "ARCHIVED"]]
  ];

  /**
   * Returns a QueryBuilder scoped to published posts only.
   *
   * @return \QueryBuilder
   */
  public static function publiclyVisible()
  {
    return self::where(['status' => 'PUBLISHED']);
  }

  /**
   * Initialises the Post with the provided attribute data.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

}

