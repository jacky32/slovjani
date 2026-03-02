<?php

declare(strict_types=1);

namespace ActiveModel {
  if (!class_exists('\\ActiveModel\\ValidationException')) {
    class ValidationException extends \Exception {}
  }
}

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
  require __DIR__ . '/../../../../lib/active_model/validations/length.php';

  use PHPUnit\Framework\TestCase;
  use ActiveModel\Validations\LengthValidator;


  final class length_test extends TestCase
  {
    private $name;
    private $description;
    private $code;
    use LengthValidator;

    public function testValidatesLengthOfNoViolation()
    {
      $this->name = 'John Doe';  // 8 chars
      $exceptions = $this->validates_length_of(['name' => ['min' => 3, 'max' => 255]]);
      $this->assertEmpty($exceptions);
    }

    public function testValidatesLengthOfMinViolation()
    {
      $this->name = 'Jo';  // 2 chars, below min of 3
      $exceptions = $this->validates_length_of(['name' => ['min' => 3]]);
      $this->assertCount(1, $exceptions);
      $expected = [
        'class' => length_test::class,
        'attribute' => 'name',
        'message' => 'errors.must_be_longer_than'
      ];
      $this->assertContains($expected, $exceptions);
    }

    public function testValidatesLengthOfMaxViolation()
    {
      $this->name = str_repeat('a', 256);  // 256 chars, above max of 255
      $exceptions = $this->validates_length_of(['name' => ['max' => 255]]);
      $this->assertCount(1, $exceptions);
      $expected = [
        'class' => length_test::class,
        'attribute' => 'name',
        'message' => 'errors.must_not_be_longer_than'
      ];
      $this->assertContains($expected, $exceptions);
    }

    public function testValidatesLengthOfExactViolation()
    {
      $this->code = 'ABC';  // 3 chars, but must be exactly 5
      $exceptions = $this->validates_length_of(['code' => ['is' => 5]]);
      $this->assertCount(1, $exceptions);
      $expected = [
        'class' => length_test::class,
        'attribute' => 'code',
        'message' => 'errors.must_be_exactly_characters_long'
      ];
      $this->assertContains($expected, $exceptions);
    }

    public function testValidatesLengthOfExactNoViolation()
    {
      $this->code = 'ABCDE';  // exactly 5 chars
      $exceptions = $this->validates_length_of(['code' => ['is' => 5]]);
      $this->assertEmpty($exceptions);
    }

    public function testValidatesLengthOfMinAndMaxBothViolated()
    {
      // A string can't violate both min and max at the same time for a single attribute,
      // but two different attributes can each violate a different constraint
      $this->name = 'Jo';             // 2 chars, below min of 8
      $this->description = str_repeat('x', 1001);  // above max of 1000
      $exceptions = $this->validates_length_of([
        'name' => ['min' => 8, 'max' => 255],
        'description' => ['min' => 8, 'max' => 1000]
      ]);
      $this->assertCount(2, $exceptions);
      $this->assertContains([
        'class' => length_test::class,
        'attribute' => 'name',
        'message' => 'errors.must_be_longer_than'
      ], $exceptions);
      $this->assertContains([
        'class' => length_test::class,
        'attribute' => 'description',
        'message' => 'errors.must_not_be_longer_than'
      ], $exceptions);
    }

    public function testValidatesLengthOfMinAndMaxSameAttributeBothViolated()
    {
      // Both min and max can fire for the same field if the string is below min AND above max
      // is not possible, but we can test that min and max checks are independent
      // Test: value exactly at min boundary - no min violation
      $this->name = 'Joh';  // exactly 3 chars = min
      $exceptions = $this->validates_length_of(['name' => ['min' => 3, 'max' => 255]]);
      $this->assertEmpty($exceptions);
    }

    public function testValidatesLengthOfAllConstraintsNoViolation()
    {
      $this->name = 'ValidName';     // 9 chars, within 3-255
      $this->description = 'A valid description that is long enough.';  // within 8-1000
      $this->code = 'ABCDE';         // exactly 5
      $exceptions = $this->validates_length_of([
        'name' => ['min' => 3, 'max' => 255],
        'description' => ['min' => 8, 'max' => 1000],
        'code' => ['is' => 5]
      ]);
      $this->assertEmpty($exceptions);
    }
  }
}
