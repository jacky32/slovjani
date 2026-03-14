<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../lib/active_model/attributes/attributes.php';

use PHPUnit\Framework\TestCase;

/**
 * Concrete test double that exposes the protected Attributes trait methods.
 *
 * PHP 8.2+ forbids redefining a trait property with a different default value, so
 * $db_attributes is NOT redeclared here.  The test's setUp() populates it via
 * Reflection so the trait's initializeAttributes() behaves correctly.
 */
if (!class_exists('AttributesTestModel')) {
  class AttributesTestModel
  {
    use Attributes;

    // $db_attributes is inherited from the Attributes trait (defaults to []).
    // setUp() overrides it via Reflection for each test.

    public function init(array $data): void
    {
      $this->initializeAttributes($data);
    }

    public function get(string $name): mixed
    {
      return $this->getAttribute($name);
    }

    public function set(string $name, mixed $value): void
    {
      $this->setAttribute($name, $value);
    }

    public function has(string $name): bool
    {
      return $this->hasAttribute($name);
    }
  }
}

final class attributes_test extends TestCase
{
  private AttributesTestModel $model;

  protected function setUp(): void
  {
    // PHP 8.2+ prohibits redefining a trait property with a different default
    // value in the class body, so we set $db_attributes via Reflection.
    $ref = new ReflectionProperty(AttributesTestModel::class, 'db_attributes');
    $ref->setAccessible(true);
    $ref->setValue(null, ['name', 'email', 'age']);
    $this->model = new AttributesTestModel();
  }

  // ---- initializeAttributes() ----

  public function testInitializeAttributesSetsKnownAttributes(): void
  {
    $this->model->init(['name' => 'Alice', 'email' => 'alice@example.com']);
    $this->assertSame('Alice', $this->model->get('name'));
    $this->assertSame('alice@example.com', $this->model->get('email'));
  }

  public function testInitializeAttributesIgnoresUnknownAttributes(): void
  {
    $this->model->init(['name' => 'Alice', 'password' => 'secret']);
    // 'password' is not in $db_attributes so it should not be stored
    $this->assertNull($this->model->get('password'));
  }

  public function testInitializeAttributesLeavesMissingAttributesAsNull(): void
  {
    $this->model->init(['name' => 'Alice']);
    $this->assertNull($this->model->get('email'));
    $this->assertNull($this->model->get('age'));
  }

  public function testInitializeAttributesWithEmptyDataLeavesAllNull(): void
  {
    $this->model->init([]);
    $this->assertNull($this->model->get('name'));
    $this->assertNull($this->model->get('email'));
  }

  // ---- getAttribute() ----

  public function testGetAttributeReturnsSetValue(): void
  {
    $this->model->set('name', 'Bob');
    $this->assertSame('Bob', $this->model->get('name'));
  }

  public function testGetAttributeReturnsNullForUnsetKnownAttribute(): void
  {
    $this->assertNull($this->model->get('name'));
  }

  public function testGetAttributeReturnsNullForAttributeNotInDbAttributes(): void
  {
    // 'nonexistent' is not declared in $db_attributes
    $this->assertNull($this->model->get('nonexistent'));
  }

  public function testGetAttributeReturnsCorrectTypeForInteger(): void
  {
    $this->model->set('age', 30);
    $this->assertSame(30, $this->model->get('age'));
  }

  // ---- setAttribute() ----

  public function testSetAttributeStoresValue(): void
  {
    $this->model->set('email', 'test@test.com');
    $this->assertSame('test@test.com', $this->model->get('email'));
  }

  public function testSetAttributeOverwritesPreviousValue(): void
  {
    $this->model->set('name', 'Charlie');
    $this->model->set('name', 'Dave');
    $this->assertSame('Dave', $this->model->get('name'));
  }

  public function testSetAttributeCanStoreNullValue(): void
  {
    $this->model->set('name', 'Alice');
    $this->model->set('name', null);
    $this->assertNull($this->model->get('name'));
  }

  // ---- hasAttribute() ----

  public function testHasAttributeReturnsTrueAfterSet(): void
  {
    $this->model->set('name', 'Alice');
    $this->assertTrue($this->model->has('name'));
  }

  public function testHasAttributeReturnsFalseWhenNotSet(): void
  {
    $this->assertFalse($this->model->has('name'));
  }

  public function testHasAttributeReturnsFalseAfterSetToNull(): void
  {
    // isset() returns false for null values, so hasAttribute returns false
    $this->model->set('name', null);
    $this->assertFalse($this->model->has('name'));
  }

  public function testInitializeFollowedByAttributeAccess(): void
  {
    $this->model->init(['name' => 'Eve', 'age' => 25]);
    $this->assertTrue($this->model->has('name'));
    $this->assertTrue($this->model->has('age'));
    $this->assertFalse($this->model->has('email'));
  }
}
