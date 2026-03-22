<?php

declare(strict_types=1);

namespace ActiveModel\Validations {
  if (!function_exists('ActiveModel\\Validations\\t')) {
    function t(string $key, array $params = []): string
    {
      return $key;
    }
  }
}

namespace {

  if (!function_exists('toSnakeCase')) {
    function toSnakeCase(string $input): string
    {
      return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
  }
  if (!function_exists('t')) {
    function t(string $key, array $params = []): string
    {
      return $key;
    }
  }
  if (!class_exists('Logger')) {
    require_once __DIR__ . '/../../lib/logger.php';
  }
  if (!class_exists('Collection')) {
    require_once __DIR__ . '/../../lib/active_model/relations/collection.php';
  }
  if (!class_exists('QueryBuilder')) {
    require_once __DIR__ . '/../../lib/active_model/relations/query_builder.php';
  }
  if (!class_exists('Pagination')) {
    require_once __DIR__ . '/../../lib/active_model/relations/pagination.php';
  }
  if (!trait_exists('Relations')) {
    require_once __DIR__ . '/../../lib/active_model/relations/relations.php';
  }
  if (!trait_exists('Attributes')) {
    require_once __DIR__ . '/../../lib/active_model/attributes/attributes.php';
  }
  if (!trait_exists('Validations')) {
    require_once __DIR__ . '/../../lib/active_model/validations/validations.php';
  }
  if (!class_exists('ActiveModel')) {
    require_once __DIR__ . '/../../lib/active_model/active_model.php';
  }

  if (!class_exists('Database')) {
    class Database
    {
      public function getConnection(): object
      {
        return new class {
          public function close(): void {}
        };
      }
    }
  }

  if (!class_exists('ApplicationRecord')) {
    require_once __DIR__ . '/../../app/models/application_record.php';
  }
  if (!class_exists('User')) {
    require_once __DIR__ . '/../../app/models/user.php';
  }
  if (!class_exists('Attachment')) {
    require_once __DIR__ . '/../../app/models/attachment.php';
  }
  if (!class_exists('Comment')) {
    require_once __DIR__ . '/../../app/models/comment.php';
  }
  if (!class_exists('Event')) {
    require_once __DIR__ . '/../../app/models/event.php';
  }

  use ActiveModel\ValidationException;
  use PHPUnit\Framework\TestCase;

  final class event_validation_test extends TestCase
  {
    public function testSaveRaisesValidationExceptionWhenDatetimeStartIsNotBeforeDatetimeEnd(): void
    {
      $event = new class([
        'creator_id' => 1,
        'name' => 'Nazev akce',
        'description' => 'Popis akce',
        'datetime_start' => '2026-03-22 12:00:00',
        'datetime_end' => '2026-03-22 12:00:00',
      ]) extends Event {
        public bool $createCalled = false;
        public bool $updateCalled = false;

        public function create()
        {
          $this->createCalled = true;
        }

        public function update()
        {
          $this->updateCalled = true;
        }
      };

      try {
        $event->save();
        $this->fail('Expected ValidationException was not thrown.');
      } catch (ValidationException $exception) {
        $this->assertFalse($event->createCalled);
        $this->assertFalse($event->updateCalled);
        $this->assertContains([
          'class' => $event::class,
          'attribute' => 'datetime_end',
          'message' => t('errors.must_be_after_datetime_start'),
        ], $exception->getValidationExceptions());
      }
    }

    public function testSavePassesAndCallsCreateWhenDatetimeStartIsBeforeDatetimeEnd(): void
    {
      $event = new class([
        'creator_id' => 1,
        'name' => 'Nazev akce',
        'description' => 'Popis akce',
        'datetime_start' => '2026-03-22 11:00:00',
        'datetime_end' => '2026-03-22 12:00:00',
      ]) extends Event {
        public bool $createCalled = false;

        public function create()
        {
          $this->createCalled = true;
        }
      };

      $event->save();
      $this->assertTrue($event->createCalled);
    }
  }
}
