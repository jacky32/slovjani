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
  require_once __DIR__ . '/../../../../lib/active_model/validations/Validations.php';

  use PHPUnit\Framework\TestCase;
  use ActiveModel\Validations\PresenceValidator;


  final class PresenceTest extends TestCase
  {
    private $name;
    private $email;
    private $description;
    use PresenceValidator;

    public function testValidatesPresenceOfNoViolation()
    {
      $this->name = 'John';
      $this->email = 'john@example.com';
      $exceptions = $this->validates_presence_of(['name', 'email']);
      $this->assertEmpty($exceptions);
    }

    public function testValidatesPresenceOfSingleAttributeViolation()
    {
      $this->name = null;
      $exceptions = $this->validates_presence_of(['name']);
      $this->assertCount(1, $exceptions);
      $expected = [
        'class' => PresenceTest::class,
        'attribute' => 'name',
        'message' => 'errors.cannot_be_blank'
      ];
      $this->assertContains($expected, $exceptions);
    }

    public function testValidatesPresenceOfMultipleAttributesAllViolated()
    {
      $this->name = null;
      $this->email = null;
      $exceptions = $this->validates_presence_of(['name', 'email']);
      $this->assertCount(2, $exceptions);
      $this->assertContains([
        'class' => PresenceTest::class,
        'attribute' => 'name',
        'message' => 'errors.cannot_be_blank'
      ], $exceptions);
      $this->assertContains([
        'class' => PresenceTest::class,
        'attribute' => 'email',
        'message' => 'errors.cannot_be_blank'
      ], $exceptions);
    }

    public function testValidatesPresenceOfPartialViolation()
    {
      $this->name = 'John';
      $this->email = null;
      $exceptions = $this->validates_presence_of(['name', 'email']);
      $this->assertCount(1, $exceptions);
      $expected = [
        'class' => PresenceTest::class,
        'attribute' => 'email',
        'message' => 'errors.cannot_be_blank'
      ];
      $this->assertContains($expected, $exceptions);
    }

    public function testValidatesPresenceOfEmptyStringTreatedAsBlank()
    {
      // The validator uses loose comparison (== null), so '' == null is true in PHP
      $this->description = '';
      $exceptions = $this->validates_presence_of(['description']);
      $this->assertCount(1, $exceptions);
      $this->assertContains([
        'class' => PresenceTest::class,
        'attribute' => 'description',
        'message' => 'errors.cannot_be_blank'
      ], $exceptions);
    }
  }
}
