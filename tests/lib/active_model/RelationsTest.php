<?php

declare(strict_types=1);

// ---- Minimal stubs so Relations can be exercised without a database ----

if (!function_exists('toSnakeCase')) {
  require_once __DIR__ . '/../../../lib/Helpers.php';
}
if (!class_exists('Logger')) {
  require_once __DIR__ . '/../../../lib/Logger.php';
}
if (!class_exists('Collection')) {
  require_once __DIR__ . '/../../../lib/active_model/relations/Collection.php';
}
if (!class_exists('QueryBuilder')) {
  require_once __DIR__ . '/../../../lib/active_model/relations/QueryBuilder.php';
}
require_once __DIR__ . '/../../../lib/active_model/relations/Relations.php';

use PHPUnit\Framework\TestCase;

// ---- Stub "model" classes used by relations ----

if (!class_exists('RelOwner')) {
  /** Simulates the parent model (e.g. Post) that holds foreign-key columns. */
  class RelOwner
  {
    use Relations;

    public int $id = 1;
    public ?int $category_id = 10;
    public ?string $resource_type = 'Post';
    public ?int $resource_id = 5;

    protected static array $relations = [
      'belongs_to' => [
        'category' => [
          'class_name' => 'RelCategory',
          'foreign_key' => 'category_id',
        ],
        'polymorphic_owner' => [
          'polymorphic' => true,
          'foreign_key' => 'resource_id',
          'foreign_type' => 'resource_type',
        ],
      ],
      'has_many' => [
        'items' => [
          'class_name' => 'RelItem',
          'foreign_key' => 'owner_id',
        ],
        'polymorphic_items' => [
          'class_name' => 'RelItem',
          'foreign_key' => 'resource_id',
          'foreign_type' => 'resource_type',
          'polymorphic' => true,
        ],
      ],
    ];

    /** Expose protected methods publicly for testing. */
    public function pubGetRelation(string $name): mixed
    {
      return $this->getRelation($name);
    }

    public function pubHasRelation(string $name): bool
    {
      return $this->hasRelation($name);
    }
  }
}

if (!class_exists('RelCategory')) {
  /** Minimal stub for belongs_to target. */
  class RelCategory
  {
    public static ?object $findResult = null;

    public static function find(mixed $id): ?object
    {
      return self::$findResult;
    }

    public static function where(array $conds): QueryBuilder
    {
      return new QueryBuilder('RelCategory');
    }
  }
}

if (!class_exists('RelItem')) {
  /** Minimal stub for has_many target. */
  class RelItem
  {
    public static function where(array $conds): QueryBuilder
    {
      return new QueryBuilder('RelItem');
    }
  }
}

// ---- Tests ----

final class RelationsTest extends TestCase
{
  private RelOwner $owner;

  protected function setUp(): void
  {
    $this->owner = new RelOwner();
    // Clear the find stub between tests
    RelCategory::$findResult = null;
  }

  // ---- hasRelation() ----

  public function testHasRelationReturnsTrueForKnownBelongsTo(): void
  {
    $this->assertTrue($this->owner->pubHasRelation('category'));
  }

  public function testHasRelationReturnsTrueForKnownHasMany(): void
  {
    $this->assertTrue($this->owner->pubHasRelation('items'));
  }

  public function testHasRelationReturnsTrueForPolymorphicBelongsTo(): void
  {
    $this->assertTrue($this->owner->pubHasRelation('polymorphic_owner'));
  }

  public function testHasRelationReturnsTrueForPolymorphicHasMany(): void
  {
    $this->assertTrue($this->owner->pubHasRelation('polymorphic_items'));
  }

  public function testHasRelationReturnsFalseForUnknownName(): void
  {
    $this->assertFalse($this->owner->pubHasRelation('nonexistent'));
  }

  public function testHasRelationReturnsFalseForEmptyString(): void
  {
    $this->assertFalse($this->owner->pubHasRelation(''));
  }

  // ---- getRelation() – belongs_to ----

  public function testGetRelationBelongsToCallsFindOnTargetClass(): void
  {
    $stub = (object)['id' => 10, 'name' => 'Science'];
    RelCategory::$findResult = $stub;

    $result = $this->owner->pubGetRelation('category');
    $this->assertSame($stub, $result);
  }

  public function testGetRelationBelongsToReturnsNullWhenRecordNotFound(): void
  {
    RelCategory::$findResult = null;
    $this->assertNull($this->owner->pubGetRelation('category'));
  }

  public function testGetRelationBelongsToIsCached(): void
  {
    $stub = (object)['id' => 10];
    RelCategory::$findResult = $stub;

    $first  = $this->owner->pubGetRelation('category');
    // Change stub: cached result should still be the original
    RelCategory::$findResult = (object)['id' => 99];
    $second = $this->owner->pubGetRelation('category');

    $this->assertSame($first, $second);
  }

  // ---- getRelation() – has_many ----

  public function testGetRelationHasManyReturnsQueryBuilder(): void
  {
    $result = $this->owner->pubGetRelation('items');
    $this->assertInstanceOf(QueryBuilder::class, $result);
  }

  public function testGetRelationHasManyIsCached(): void
  {
    $first  = $this->owner->pubGetRelation('items');
    $second = $this->owner->pubGetRelation('items');
    $this->assertSame($first, $second);
  }

  public function testGetRelationHasManyPolymorphicReturnsQueryBuilder(): void
  {
    $result = $this->owner->pubGetRelation('polymorphic_items');
    $this->assertInstanceOf(QueryBuilder::class, $result);
  }

  // ---- getRelation() – unknown name ----

  public function testGetRelationReturnsNullForUnknownName(): void
  {
    $this->assertNull($this->owner->pubGetRelation('nonexistent'));
  }

  // ---- setHasManyRelation() via Reflection ----

  public function testSetHasManyRelationBuildsCorrectQueryBuilder(): void
  {
    $ref = new ReflectionMethod($this->owner, 'setHasManyRelation');
    $ref->setAccessible(true);
    $qb = $ref->invoke($this->owner, [
      'class_name'  => 'RelItem',
      'foreign_key' => 'owner_id',
    ]);
    $this->assertInstanceOf(QueryBuilder::class, $qb);
  }

  public function testSetHasManyRelationPolymorphicBuildsQueryBuilder(): void
  {
    $ref = new ReflectionMethod($this->owner, 'setHasManyRelation');
    $ref->setAccessible(true);
    $qb = $ref->invoke($this->owner, [
      'class_name'  => 'RelItem',
      'foreign_key' => 'resource_id',
      'foreign_type' => 'resource_type',
      'polymorphic' => true,
    ]);
    $this->assertInstanceOf(QueryBuilder::class, $qb);
  }
}
