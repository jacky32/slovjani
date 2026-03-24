<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Voting model with questions, status lifecycle, and participation helpers.
 *
 * @package Models
 */
class Voting extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'datetime_start',
    'datetime_end',
    'name',
    'status',
    'description',
    'creator_id',
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
      'questions' => [
        'class_name' => Question::class,
        'foreign_key' => 'voting_id'
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
    'presence' => ['datetime_start', 'datetime_end', 'name', 'status', 'description', 'creator_id'],
    'length' => [["name" => ["min" => 8, "max" => 255], "description" => ["min" => 8, "max" => 1000]]],
    'inclusion' => ['status' => ['DRAFT', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED']]
  ];

  /**
   * Initialises the Voting with the provided attribute data.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  // Methods

  /**
   * Checks whether a user has already submitted at least one answer in this voting.
   *
   * @param int $user_id The ID of the user to check.
   * @return bool True if the user has voted, false otherwise.
   */
  public function hasUserVoted($user_id)
  {
    $question_ids = $this->questions->pluck('id');
    if (empty($question_ids)) {
      return false;
    }
    $users_question = UsersQuestion::where([
      'question_id' => $question_ids,
      'user_id' => $user_id
    ])->first();

    return $users_question !== null;
  }
}

class_alias(__NAMESPACE__ . '\\Voting', 'Voting');
