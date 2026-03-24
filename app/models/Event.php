<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Event domain model with visibility and scheduling attributes.
 *
 * @package Models
 */
class Event extends ApplicationRecord
{
  protected static array $db_attributes = [
    'id',
    'creator_id',
    'name',
    'description',
    'datetime_start',
    'datetime_end',
    'google_calendar_event_id',
    'is_publicly_visible',
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
    'presence' => ['creator_id', 'name', 'description', 'datetime_start'],
    'length' => ["name" => ["min" => 4, "max" => 255], "description" => ["min" => 4, "max" => 5000]]
  ];

  protected static array $validation_callbacks = [
    'validate_datetime_range'
  ];

  /**
   * Initialises the Event with the provided attribute data.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    parent::__construct($data, self::$db_attributes, self::$relations);
  }

  /**
   * Ensures datetime_start is strictly earlier than datetime_end.
   *
   * @return array<int, array{class: string, attribute: string, message: string}>
   */
  protected function validate_datetime_range(): array
  {
    $caught_exceptions = [];

    $start_timestamp = strtotime((string) $this->datetime_start);
    $end_timestamp = strtotime((string) $this->datetime_end);
    if (
      $this->datetime_start !== null
      && $this->datetime_end !== null
      && $start_timestamp !== false
      && $end_timestamp !== false
      && $start_timestamp >= $end_timestamp
    ) {
      $caught_exceptions[] = [
        'class' => static::class,
        'attribute' => 'datetime_end',
        'message' => t('errors.must_be_after_datetime_start')
      ];
    }

    return $caught_exceptions;
  }

}

class_alias(__NAMESPACE__ . '\\Event', 'Event');
