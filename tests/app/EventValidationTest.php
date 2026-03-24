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

namespace App\Services {
  if (!class_exists('App\\Services\\Database')) {
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
}

namespace {

  if (!function_exists('yaml_parse_file')) {
    function yaml_parse_file(string $filename): array
    {
      return [
        'errors' => [
          'validation_failed' => 'errors.validation_failed',
          'must_be_after_datetime_start' => 'errors.must_be_after_datetime_start',
        ],
      ];
    }
  }

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
    require_once __DIR__ . '/../../lib/Logger.php';
  }
  if (!class_exists('Collection')) {
    require_once __DIR__ . '/../../lib/active_model/relations/Collection.php';
  }
  if (!class_exists('QueryBuilder')) {
    require_once __DIR__ . '/../../lib/active_model/relations/QueryBuilder.php';
  }
  if (!class_exists('Pagination')) {
    require_once __DIR__ . '/../../lib/active_model/relations/Pagination.php';
  }
  if (!trait_exists('Relations')) {
    require_once __DIR__ . '/../../lib/active_model/relations/Relations.php';
  }
  if (!trait_exists('Attributes')) {
    require_once __DIR__ . '/../../lib/active_model/attributes/Attributes.php';
  }
  if (!trait_exists('Validations')) {
    require_once __DIR__ . '/../../lib/active_model/validations/Validations.php';
  }
  if (!class_exists('ActiveModel')) {
    require_once __DIR__ . '/../../lib/active_model/ActiveModel.php';
  }

  if (!class_exists('App\\Models\\ApplicationRecord')) {
    require_once __DIR__ . '/../../app/models/ApplicationRecord.php';
  }
  if (!class_exists('App\\Models\\User')) {
    require_once __DIR__ . '/../../app/models/User.php';
  }
  if (!class_exists('App\\Models\\Attachment')) {
    require_once __DIR__ . '/../../app/models/Attachment.php';
  }
  if (!class_exists('App\\Models\\Comment')) {
    require_once __DIR__ . '/../../app/models/Comment.php';
  }
  if (!class_exists('App\\Models\\Event')) {
    require_once __DIR__ . '/../../app/models/Event.php';
  }

  use App\Models\Event;
  use ActiveModel\ValidationException;
  use PHPUnit\Framework\TestCase;

  final class EventValidationTest extends TestCase
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
