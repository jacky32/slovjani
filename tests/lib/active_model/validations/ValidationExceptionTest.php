<?php

declare(strict_types=1);

namespace {
  require_once __DIR__ . '/../../../../lib/active_model/validations/Validations.php';

  use PHPUnit\Framework\TestCase;
  use ActiveModel\ValidationException;

  /**
   * Tests for ActiveModel\ValidationException.
   *
   * ValidationException is a pure value object – no DB access required.
   */
  final class ValidationExceptionTest extends TestCase
  {
    // ---- constructor & getValidationExceptions() ----

    public function testGetValidationExceptionsReturnsEmptyArrayByDefault(): void
    {
      $ex = new ValidationException('failed');
      $this->assertSame([], $ex->getValidationExceptions());
    }

    public function testGetValidationExceptionsReturnsSingleViolation(): void
    {
      $violations = [['class' => 'Post', 'attribute' => 'name', 'message' => 'errors.cannot_be_blank']];
      $ex = new ValidationException('failed', 0, null, $violations);
      $this->assertSame($violations, $ex->getValidationExceptions());
    }

    public function testGetValidationExceptionsReturnsAllViolations(): void
    {
      $violations = [
        ['class' => 'Post', 'attribute' => 'name',   'message' => 'errors.cannot_be_blank'],
        ['class' => 'Post', 'attribute' => 'status',  'message' => 'errors.must_be_included_in'],
        ['class' => 'Post', 'attribute' => 'creator_id', 'message' => 'errors.cannot_be_blank'],
      ];
      $ex = new ValidationException('failed', 0, null, $violations);
      $this->assertCount(3, $ex->getValidationExceptions());
      $this->assertSame($violations, $ex->getValidationExceptions());
    }

    public function testExceptionMessageIsPreserved(): void
    {
      $ex = new ValidationException('Validation failed.');
      $this->assertSame('Validation failed.', $ex->getMessage());
    }

    public function testExceptionCodeIsPreserved(): void
    {
      $ex = new ValidationException('error', 422);
      $this->assertSame(422, $ex->getCode());
    }

    public function testPreviousExceptionIsPreserved(): void
    {
      $prev = new \RuntimeException('previous');
      $ex = new ValidationException('failed', 0, $prev);
      $this->assertSame($prev, $ex->getPrevious());
    }

    public function testValidationExceptionIsAnException(): void
    {
      $ex = new ValidationException();
      $this->assertInstanceOf(\Exception::class, $ex);
    }

    // ---- re-calls are idempotent ----

    public function testGetValidationExceptionsIsIdempotent(): void
    {
      $violations = [['class' => 'User', 'attribute' => 'email', 'message' => 'errors.must_be_unique']];
      $ex = new ValidationException('failed', 0, null, $violations);
      $this->assertSame($ex->getValidationExceptions(), $ex->getValidationExceptions());
    }
  }
}
