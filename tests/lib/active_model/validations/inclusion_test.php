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
  require __DIR__ . '/../../../../lib/active_model/validations/inclusion.php';

  use PHPUnit\Framework\TestCase;
  use ActiveModel\Validations\InclusionValidator;


  final class inclusion_test extends TestCase
  {
    private $chosen_option;
    private $role;
    use InclusionValidator;

    public function testValidatesInclusionOfNoViolation()
    {
      $this->chosen_option = 'yes';
      $exceptions = $this->validates_inclusion_of(['chosen_option' => ['yes', 'no', 'abstain']]);
      $this->assertEmpty($exceptions);
    }

    public function testValidatesInclusionOfSingleAttributeViolation()
    {
      $this->chosen_option = 'maybe';
      $allowed = ['yes', 'no', 'abstain'];
      $exceptions = $this->validates_inclusion_of(['chosen_option' => $allowed]);
      $this->assertCount(1, $exceptions);
      $expected = [
        'class' => inclusion_test::class,
        'attribute' => 'chosen_option',
        'message' => 'errors.must_be_included_in'
      ];
      $this->assertContains($expected, $exceptions);
    }

    public function testValidatesInclusionOfMultipleAttributesAllViolated()
    {
      $this->chosen_option = 'maybe';
      $this->role = 'superuser';
      $exceptions = $this->validates_inclusion_of([
        'chosen_option' => ['yes', 'no', 'abstain'],
        'role' => ['admin', 'member', 'guest']
      ]);
      $this->assertCount(2, $exceptions);
      $this->assertContains([
        'class' => inclusion_test::class,
        'attribute' => 'chosen_option',
        'message' => 'errors.must_be_included_in'
      ], $exceptions);
      $this->assertContains([
        'class' => inclusion_test::class,
        'attribute' => 'role',
        'message' => 'errors.must_be_included_in'
      ], $exceptions);
    }

    public function testValidatesInclusionOfPartialViolation()
    {
      $this->chosen_option = 'yes';
      $this->role = 'superuser';
      $exceptions = $this->validates_inclusion_of([
        'chosen_option' => ['yes', 'no', 'abstain'],
        'role' => ['admin', 'member', 'guest']
      ]);
      $this->assertCount(1, $exceptions);
      $expected = [
        'class' => inclusion_test::class,
        'attribute' => 'role',
        'message' => 'errors.must_be_included_in'
      ];
      $this->assertContains($expected, $exceptions);
    }

    public function testValidatesInclusionOfAllValuesInList()
    {
      $this->chosen_option = 'abstain';
      $this->role = 'admin';
      $exceptions = $this->validates_inclusion_of([
        'chosen_option' => ['yes', 'no', 'abstain'],
        'role' => ['admin', 'member', 'guest']
      ]);
      $this->assertEmpty($exceptions);
    }
  }
}
