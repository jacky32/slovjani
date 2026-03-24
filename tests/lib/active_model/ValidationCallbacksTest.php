<?php

declare(strict_types=1);

namespace {
  if (!function_exists('t')) {
    function t(string $key, array $params = []): string
    {
      return $key;
    }
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

  require_once __DIR__ . '/../../../lib/active_model/ActiveModel.php';

  use ActiveModel\ValidationException;
  use PHPUnit\Framework\TestCase;

  final class ValidationCallbacksTest extends TestCase
  {
    public function testValidateCallsRegisteredCallbackAndPassesWhenNoViolations(): void
    {
      $model = new class extends ActiveModel {
        protected static array $validation_callbacks = ['validate_custom'];

        public bool $called = false;

        protected function validate_custom(): array
        {
          $this->called = true;
          return [];
        }
      };

      $model->validate();
      $this->assertTrue($model->called);
    }

    public function testValidateMergesViolationsFromRegisteredCallback(): void
    {
      $model = new class extends ActiveModel {
        protected static array $validation_callbacks = ['validate_custom'];

        protected function validate_custom(): array
        {
          return [[
            'class' => static::class,
            'attribute' => 'datetime_end',
            'message' => 'errors.must_be_after_datetime_start',
          ]];
        }
      };

      try {
        $model->validate();
        $this->fail('Expected ValidationException was not thrown.');
      } catch (ValidationException $exception) {
        $this->assertContains([
          'class' => $model::class,
          'attribute' => 'datetime_end',
          'message' => 'errors.must_be_after_datetime_start',
        ], $exception->getValidationExceptions());
      }
    }

    public function testValidateThrowsLogicExceptionWhenCallbackMethodIsMissing(): void
    {
      $model = new class extends ActiveModel {
        protected static array $validation_callbacks = ['missing_callback_method'];
      };

      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage("Validation callback 'missing_callback_method' is not defined");

      $model->validate();
    }

    public function testValidateThrowsLogicExceptionWhenCallbackDoesNotReturnArray(): void
    {
      $model = new class extends ActiveModel {
        protected static array $validation_callbacks = ['validate_custom'];

        protected function validate_custom(): string
        {
          return 'invalid-return-type';
        }
      };

      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage("Validation callback 'validate_custom'");
      $this->expectExceptionMessage('must return an array of validation exceptions');

      $model->validate();
    }
  }
}
