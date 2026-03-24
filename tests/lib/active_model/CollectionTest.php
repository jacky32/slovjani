<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../lib/active_model/relations/Collection.php';

use PHPUnit\Framework\TestCase;

/**
 * Tests for the Collection class.
 *
 * All items are stdClass objects so type-hint contracts are respected.
 */
final class CollectionTest extends TestCase
{
  /** Build a plain object with the given properties. */
  private function item(array $props): object
  {
    return (object) $props;
  }

  // ---- first() ----

  public function testFirstReturnsFirstItem(): void
  {
    $a = $this->item(['id' => 1]);
    $b = $this->item(['id' => 2]);
    $col = new Collection([$a, $b]);
    $this->assertSame($a, $col->first());
  }

  public function testFirstReturnsNullOnEmptyCollection(): void
  {
    $this->assertNull((new Collection())->first());
  }

  // ---- last() ----

  public function testLastReturnsLastItem(): void
  {
    $a = $this->item(['id' => 1]);
    $b = $this->item(['id' => 2]);
    $col = new Collection([$a, $b]);
    $this->assertSame($b, $col->last());
  }

  public function testLastReturnsNullOnEmptyCollection(): void
  {
    $this->assertNull((new Collection())->last());
  }

  public function testLastOnSingleItemCollection(): void
  {
    $a = $this->item(['id' => 1]);
    $col = new Collection([$a]);
    $this->assertSame($a, $col->last());
  }

  // ---- isEmpty() / isNotEmpty() ----

  public function testIsEmptyReturnsTrueForEmptyCollection(): void
  {
    $this->assertTrue((new Collection())->isEmpty());
  }

  public function testIsEmptyReturnsFalseForNonEmptyCollection(): void
  {
    $this->assertFalse((new Collection([$this->item(['id' => 1])]))->isEmpty());
  }

  public function testIsNotEmptyReturnsTrueForNonEmptyCollection(): void
  {
    $this->assertTrue((new Collection([$this->item(['id' => 1])]))->isNotEmpty());
  }

  public function testIsNotEmptyReturnsFalseForEmptyCollection(): void
  {
    $this->assertFalse((new Collection())->isNotEmpty());
  }

  // ---- count() ----

  public function testCountReturnsZeroForEmptyCollection(): void
  {
    $this->assertSame(0, (new Collection())->count());
  }

  public function testCountReturnsCorrectNumber(): void
  {
    $items = [$this->item(['id' => 1]), $this->item(['id' => 2]), $this->item(['id' => 3])];
    $this->assertSame(3, (new Collection($items))->count());
  }

  // ---- map() ----

  public function testMapTransformsItems(): void
  {
    $col = new Collection([$this->item(['value' => 1]), $this->item(['value' => 2])]);
    $doubled = $col->map(fn($item) => (object)['value' => $item->value * 2]);
    $this->assertSame(2, $doubled->toArray()[0]->value);
    $this->assertSame(4, $doubled->toArray()[1]->value);
  }

  public function testMapReturnsNewCollection(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $mapped = $col->map(fn($item) => $item);
    $this->assertNotSame($col, $mapped);
    $this->assertInstanceOf(Collection::class, $mapped);
  }

  // ---- filter() ----

  public function testFilterKeepsMatchingItems(): void
  {
    $col = new Collection([
      $this->item(['id' => 1, 'active' => true]),
      $this->item(['id' => 2, 'active' => false]),
      $this->item(['id' => 3, 'active' => true]),
    ]);
    $filtered = $col->filter(fn($item) => $item->active);
    $this->assertSame(2, $filtered->count());
    $this->assertSame(1, $filtered->toArray()[0]->id);
    $this->assertSame(3, $filtered->toArray()[1]->id);
  }

  public function testFilterReturnsEmptyCollectionWhenNothingMatches(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $filtered = $col->filter(fn($item) => false);
    $this->assertTrue($filtered->isEmpty());
  }

  public function testFilterReindexesResult(): void
  {
    $col = new Collection([$this->item(['id' => 1]), $this->item(['id' => 2])]);
    $filtered = $col->filter(fn($item) => $item->id === 2);
    // Re-indexed: first item should be at index 0
    $this->assertSame(2, $filtered->toArray()[0]->id);
  }

  // ---- find() ----

  public function testFindReturnsMatchingItem(): void
  {
    $target = $this->item(['id' => 5]);
    $col = new Collection([$this->item(['id' => 1]), $target, $this->item(['id' => 9])]);
    $found = $col->find(fn($item) => $item->id === 5);
    $this->assertSame($target, $found);
  }

  public function testFindReturnsNullWhenNotFound(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $this->assertNull($col->find(fn($item) => $item->id === 99));
  }

  public function testFindReturnsFirstMatch(): void
  {
    $first  = $this->item(['type' => 'x', 'order' => 1]);
    $second = $this->item(['type' => 'x', 'order' => 2]);
    $col = new Collection([$first, $second]);
    $found = $col->find(fn($item) => $item->type === 'x');
    $this->assertSame($first, $found);
  }

  // ---- pluck() ----

  public function testPluckExtractsAttribute(): void
  {
    $col = new Collection([
      $this->item(['id' => 1, 'name' => 'Alice']),
      $this->item(['id' => 2, 'name' => 'Bob']),
    ]);
    $this->assertSame(['Alice', 'Bob'], $col->pluck('name'));
  }

  public function testPluckReturnsNullForMissingAttribute(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $this->assertSame([null], $col->pluck('nonexistent'));
  }

  // ---- toArray() ----

  public function testToArrayReturnsRawArray(): void
  {
    $items = [$this->item(['id' => 1]), $this->item(['id' => 2])];
    $col = new Collection($items);
    $this->assertSame($items, $col->toArray());
  }

  public function testToArrayOnEmptyCollectionReturnsEmptyArray(): void
  {
    $this->assertSame([], (new Collection())->toArray());
  }

  // ---- each() ----

  public function testEachIteratesOverAllItems(): void
  {
    $visited = [];
    $col = new Collection([
      $this->item(['id' => 10]),
      $this->item(['id' => 20]),
    ]);
    $col->each(function ($item) use (&$visited) {
      $visited[] = $item->id;
    });
    $this->assertSame([10, 20], $visited);
  }

  public function testEachReturnsSelf(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $this->assertSame($col, $col->each(fn($item) => null));
  }

  public function testEachPassesKeyToCallback(): void
  {
    $keys = [];
    $col = new Collection([$this->item(['id' => 1]), $this->item(['id' => 2])]);
    $col->each(function ($item, $key) use (&$keys) {
      $keys[] = $key;
    });
    $this->assertSame([0, 1], $keys);
  }

  // ---- getIterator() / foreach ----

  public function testCollectionIsIterable(): void
  {
    $items = [$this->item(['id' => 1]), $this->item(['id' => 2])];
    $col = new Collection($items);
    $iterated = [];
    foreach ($col as $item) {
      $iterated[] = $item;
    }
    $this->assertSame($items, $iterated);
  }

  public function testGetIteratorReturnsArrayIterator(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $this->assertInstanceOf(\ArrayIterator::class, $col->getIterator());
  }

  // ---- ArrayAccess ----

  public function testOffsetExistsReturnsTrueForValidIndex(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $this->assertTrue(isset($col[0]));
  }

  public function testOffsetExistsReturnsFalseForInvalidIndex(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $this->assertFalse(isset($col[5]));
  }

  public function testOffsetGetReturnsItemAtIndex(): void
  {
    $item = $this->item(['id' => 42]);
    $col = new Collection([$item]);
    $this->assertSame($item, $col[0]);
  }

  public function testOffsetGetReturnsNullForInvalidIndex(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $this->assertNull($col[99]);
  }

  public function testOffsetSetReplacesItemAtIndex(): void
  {
    $original = $this->item(['id' => 1]);
    $replacement = $this->item(['id' => 99]);
    $col = new Collection([$original]);
    $col[0] = $replacement;
    $this->assertSame($replacement, $col[0]);
  }

  public function testOffsetSetWithNullAppendsItem(): void
  {
    $col = new Collection([$this->item(['id' => 1])]);
    $new = $this->item(['id' => 2]);
    $col[] = $new;
    $this->assertSame(2, $col->count());
    $this->assertSame($new, $col[1]);
  }

  public function testOffsetUnsetRemovesItem(): void
  {
    $col = new Collection([$this->item(['id' => 1]), $this->item(['id' => 2])]);
    unset($col[0]);
    // After unset the key 0 is gone; key 1 still present
    $this->assertFalse(isset($col[0]));
    $this->assertTrue(isset($col[1]));
  }
}
