<?php

declare(strict_types=1);

// Stub t() in the validator's namespace so trait code resolves it without yaml
namespace ActiveModel\Validations {
  if (!function_exists('ActiveModel\\Validations\\t')) {
    function t(string $key, array $params = []): string
    {
      return $key;
    }
  }
}

namespace {
  require_once __DIR__ . '/../../../../lib/active_model/validations/validations.php';

  use PHPUnit\Framework\TestCase;
  use ActiveModel\Validations\UniquenessValidator;


  final class uniqueness_test extends TestCase
  {
    private $user_id;
    private $question_id;
    use UniquenessValidator;

    static $composite_primary_key = ['user_id', 'question_id'];

    public function testGetIdConditionForCompositeKey()
    {
      $this->user_id = 1;
      $this->question_id = 2;
      $this->assertEquals(" AND (user_id != '1' AND question_id != '2')", $this->getIdCondition());
    }

    public function testGetConditionStringFromAttributesForCompositeKey()
    {
      $this->user_id = 1;
      $this->question_id = 2;
      $this->assertEquals("user_id = '1'", $this->getConditionStringFromAttributes('user_id'));
      $this->assertEquals("question_id = '2'", $this->getConditionStringFromAttributes('question_id'));
      $this->assertEquals("user_id = '1' AND question_id = '2'", $this->getConditionStringFromAttributes(['user_id', 'question_id']));
    }

    public function testAddViolationToExceptionsForCompositeKey()
    {
      $caught_exceptions = [];
      $attribute = ['user_id', 'question_id'];
      $row = ['count' => 1]; // Simulate a violation of uniqueness
      $this->addViolationToExceptions($caught_exceptions, $attribute, $row);
      $expected_exception = [
        'class' => uniqueness_test::class,
        'attribute' => 'user_id',
        'message' => 'errors.combination_must_be_unique'
      ];
      $this->assertContains($expected_exception, $caught_exceptions);
      $expected_exception = [
        'class' => uniqueness_test::class,
        'attribute' => 'question_id',
        'message' => 'errors.combination_must_be_unique'
      ];
      $this->assertContains($expected_exception, $caught_exceptions);
    }

    public function testAddViolationToExceptionsForSingleAttribute()
    {
      $caught_exceptions = [];
      $attribute = 'email';
      $row = ['count' => 1]; // Simulate a violation of uniqueness
      $this->addViolationToExceptions($caught_exceptions, $attribute, $row);
      $expected_exception = [
        'class' => uniqueness_test::class,
        'attribute' => $attribute,
        'message' => 'errors.must_be_unique'
      ];
      $this->assertContains($expected_exception, $caught_exceptions);
    }

    public function testAddViolationToExceptionsNoViolation()
    {
      $caught_exceptions = [];
      $attribute = ['user_id', 'question_id'];
      $row = ['count' => 0]; // Simulate no violation of uniqueness
      $this->addViolationToExceptions($caught_exceptions, $attribute, $row);
      $this->assertEmpty($caught_exceptions);
    }
  }
}
