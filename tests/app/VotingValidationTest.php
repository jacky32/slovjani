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
          'voting_cannot_start_without_questions' => 'errors.voting_cannot_start_without_questions',
          'voting_cannot_start_before_datetime_start' => 'errors.voting_cannot_start_before_datetime_start',
          'voting_cannot_end_before_datetime_end_or_all_users_voted' => 'errors.voting_cannot_end_before_datetime_end_or_all_users_voted',
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
  if (!class_exists('App\\Models\\Voting')) {
    require_once __DIR__ . '/../../app/models/Voting.php';
  }

  use ActiveModel\ValidationException;
  use App\Models\Voting;
  use PHPUnit\Framework\TestCase;

  final class VotingValidationTest extends TestCase
  {
    public function testSaveRaisesValidationExceptionWhenStartingWithoutQuestions(): void
    {
      $voting = new class([
        'creator_id' => 1,
        'name' => 'Nazev hlasovani',
        'description' => 'Popis hlasovani',
        'datetime_start' => '2026-03-22 12:00:00',
        'datetime_end' => '2026-03-23 12:00:00',
        'status' => 'IN_PROGRESS',
      ]) extends Voting {
        public bool $createCalled = false;

        public function create()
        {
          $this->createCalled = true;
        }

        protected function currentTimestamp(): int
        {
          return strtotime('2026-03-22 12:00:00');
        }

        protected function isTransitioningToStatus(string $target_status): bool
        {
          return $target_status === 'IN_PROGRESS';
        }

        protected function hasAtLeastOneQuestion(): bool
        {
          return false;
        }
      };

      try {
        $voting->save();
        $this->fail('Expected ValidationException was not thrown.');
      } catch (ValidationException $exception) {
        $this->assertFalse($voting->createCalled);
        $this->assertContains([
          'class' => $voting::class,
          'attribute' => 'status',
          'message' => t('errors.voting_cannot_start_without_questions'),
        ], $exception->getValidationExceptions());
      }
    }

    public function testSaveRaisesValidationExceptionWhenStartingBeforeDatetimeStart(): void
    {
      $voting = new class([
        'creator_id' => 1,
        'name' => 'Nazev hlasovani',
        'description' => 'Popis hlasovani',
        'datetime_start' => '2026-03-22 12:00:00',
        'datetime_end' => '2026-03-23 12:00:00',
        'status' => 'IN_PROGRESS',
      ]) extends Voting {
        public bool $createCalled = false;
        protected bool $startTransition = true;

        public function create()
        {
          $this->createCalled = true;
        }

        protected function currentTimestamp(): int
        {
          return strtotime('2026-03-22 11:00:00');
        }

        protected function isTransitioningToStatus(string $target_status): bool
        {
          return $this->startTransition && $target_status === 'IN_PROGRESS';
        }

        protected function hasAtLeastOneQuestion(): bool
        {
          return true;
        }
      };

      try {
        $voting->save();
        $this->fail('Expected ValidationException was not thrown.');
      } catch (ValidationException $exception) {
        $this->assertFalse($voting->createCalled);
        $this->assertContains([
          'class' => $voting::class,
          'attribute' => 'status',
          'message' => t('errors.voting_cannot_start_before_datetime_start'),
        ], $exception->getValidationExceptions());
      }
    }

    public function testSaveRaisesValidationExceptionWhenEndingBeforeDatetimeEndAndNotAllUsersVoted(): void
    {
      $voting = new class([
        'creator_id' => 1,
        'name' => 'Nazev hlasovani',
        'description' => 'Popis hlasovani',
        'datetime_start' => '2026-03-22 12:00:00',
        'datetime_end' => '2026-03-23 12:00:00',
        'status' => 'COMPLETED',
      ]) extends Voting {
        public bool $createCalled = false;

        public function create()
        {
          $this->createCalled = true;
        }

        protected function currentTimestamp(): int
        {
          return strtotime('2026-03-22 20:00:00');
        }

        protected function isTransitioningToStatus(string $target_status): bool
        {
          return $target_status === 'COMPLETED';
        }

        protected function countEligibleUsersForCompletion(): int
        {
          return 5;
        }

        protected function countDistinctUsersWhoVoted(): int
        {
          return 4;
        }
      };

      try {
        $voting->save();
        $this->fail('Expected ValidationException was not thrown.');
      } catch (ValidationException $exception) {
        $this->assertFalse($voting->createCalled);
        $this->assertContains([
          'class' => $voting::class,
          'attribute' => 'status',
          'message' => t('errors.voting_cannot_end_before_datetime_end_or_all_users_voted'),
        ], $exception->getValidationExceptions());
      }
    }

    public function testSavePassesWhenEndingBeforeDatetimeEndButAllUsersVoted(): void
    {
      $voting = new class([
        'creator_id' => 1,
        'name' => 'Nazev hlasovani',
        'description' => 'Popis hlasovani',
        'datetime_start' => '2026-03-22 12:00:00',
        'datetime_end' => '2026-03-23 12:00:00',
        'status' => 'COMPLETED',
      ]) extends Voting {
        public bool $createCalled = false;

        public function create()
        {
          $this->createCalled = true;
        }

        protected function currentTimestamp(): int
        {
          return strtotime('2026-03-22 20:00:00');
        }

        protected function isTransitioningToStatus(string $target_status): bool
        {
          return $target_status === 'COMPLETED';
        }

        protected function countEligibleUsersForCompletion(): int
        {
          return 5;
        }

        protected function countDistinctUsersWhoVoted(): int
        {
          return 5;
        }
      };

      $voting->save();
      $this->assertTrue($voting->createCalled);
    }
  }
}
