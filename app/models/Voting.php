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

  protected static array $validation_callbacks = [
    'validate_status_lifecycle'
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

  /**
   * Validates lifecycle transitions for starting and ending voting.
   *
   * @return array<int, array{class: string, attribute: string, message: string}>
   */
  protected function validate_status_lifecycle(): array
  {
    $caught_exceptions = [];

    if ($this->isTransitioningToStatus('IN_PROGRESS')) {
      if (!$this->hasAtLeastOneQuestion()) {
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => 'status',
          'message' => t('errors.voting_cannot_start_without_questions')
        ];
      }

      if (!$this->hasReachedStartDatetime()) {
        $caught_exceptions[] = [
          'class' => static::class,
          'attribute' => 'status',
          'message' => t('errors.voting_cannot_start_before_datetime_start')
        ];
      }
    }

    if ($this->isTransitioningToStatus('COMPLETED') && !$this->canBeCompletedNow()) {
      $caught_exceptions[] = [
        'class' => static::class,
        'attribute' => 'status',
        'message' => t('errors.voting_cannot_end_before_datetime_end_or_all_users_voted')
      ];
    }

    return $caught_exceptions;
  }

  /**
   * Checks whether this save attempts a transition into a given status.
   *
   * @param string $target_status
   * @return bool
   */
  protected function isTransitioningToStatus(string $target_status): bool
  {
    if ($this->status !== $target_status) {
      return false;
    }

    if ($this->id === null) {
      return true;
    }

    $persisted = self::find($this->id);
    if ($persisted === null) {
      return true;
    }

    return $persisted->status !== $target_status;
  }

  /**
   * Determines whether the current time is at or after datetime_start.
   */
  protected function hasReachedStartDatetime(): bool
  {
    $start_timestamp = strtotime((string) $this->datetime_start);

    return $this->datetime_start !== null
      && $start_timestamp !== false
      && $this->currentTimestamp() >= $start_timestamp;
  }

  /**
   * Determines whether the voting can be ended now.
   */
  protected function canBeCompletedNow(): bool
  {
    return $this->hasReachedEndDatetime() || $this->haveAllUsersVoted();
  }

  /**
   * Determines whether the current time is at or after datetime_end.
   */
  protected function hasReachedEndDatetime(): bool
  {
    $end_timestamp = strtotime((string) $this->datetime_end);

    return $this->datetime_end !== null
      && $end_timestamp !== false
      && $this->currentTimestamp() >= $end_timestamp;
  }

  /**
   * Returns true when voting includes at least one question.
   */
  protected function hasAtLeastOneQuestion(): bool
  {
    return !empty($this->questions->pluck('id'));
  }

  /**
   * Returns true when every user has submitted at least one answer.
   */
  protected function haveAllUsersVoted(): bool
  {
    $total_users = $this->countEligibleUsersForCompletion();
    if ($total_users === 0) {
      return false;
    }

    return $this->countDistinctUsersWhoVoted() >= $total_users;
  }

  /**
   * Returns UNIX timestamp for the current moment.
   */
  protected function currentTimestamp(): int
  {
    return time();
  }

  /**
   * Counts users eligible for the "all users voted" completion shortcut.
   */
  protected function countEligibleUsersForCompletion(): int
  {
    return User::withAnyRole()->count();
  }

  /**
   * Counts distinct users who submitted at least one answer in this voting.
   */
  protected function countDistinctUsersWhoVoted(): int
  {
    $question_ids = $this->questions->pluck('id');
    if (empty($question_ids)) {
      return 0;
    }

    $user_ids = UsersQuestion::where(['question_id' => $question_ids])->pluck('user_id');

    return count(array_unique(array_map('intval', $user_ids)));
  }
}
