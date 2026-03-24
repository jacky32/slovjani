<?php

declare(strict_types=1);

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
require_once __DIR__ . '/../../../lib/active_model/relations/Pagination.php';

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for Pagination.
 *
 * Pagination only performs arithmetic on data returned by a QueryBuilder, so
 * a PHPUnit mock is the cleanest approach – no database needed.
 */
final class PaginationTest extends TestCase
{
  /**
   * Build a mock QueryBuilder that:
   *   - returns $totalCount from count()
   *   - returns $ids from pluck('id')
   *   - returns a Collection of empty objects from get()
   *   - is chainable (limit/offset return $this)
   */
  private function mockQuery(int $totalCount, array $ids = []): MockObject
  {
    $mock = $this->getMockBuilder(QueryBuilder::class)
      ->setConstructorArgs(['MockModel'])
      ->onlyMethods(['count', 'pluck', 'limit', 'offset', 'get'])
      ->getMock();

    $mock->method('count')->willReturn($totalCount);
    $mock->method('pluck')->willReturn($ids);

    // limit() and offset() must return the mock itself so chaining works
    $mock->method('limit')->willReturnSelf();
    $mock->method('offset')->willReturnSelf();

    // get() returns an empty Collection (items themselves are irrelevant here)
    $mock->method('get')->willReturn(new Collection([]));

    return $mock;
  }

  // ---- total_count ----

  public function testTotalCountMatchesQueryCount(): void
  {
    $p = new Pagination($this->mockQuery(37));
    $this->assertSame(37, $p->total_count);
  }

  public function testTotalCountZero(): void
  {
    $p = new Pagination($this->mockQuery(0));
    $this->assertSame(0, $p->total_count);
  }

  // ---- total_pages ----

  public function testTotalPagesCalculatedCorrectlyExactDivision(): void
  {
    $p = new Pagination($this->mockQuery(30)); // 30 / 10 = 3
    $this->assertEquals(3, $p->total_pages);
  }

  public function testTotalPagesRoundsUp(): void
  {
    $p = new Pagination($this->mockQuery(21)); // ceil(21/10) = 3
    $this->assertEquals(3, $p->total_pages);
  }

  public function testTotalPagesIsAtLeastOneWhenCountIsZero(): void
  {
    $p = new Pagination($this->mockQuery(0));
    $this->assertSame(1, $p->total_pages);
  }

  public function testTotalPagesOneForSingleRecord(): void
  {
    $p = new Pagination($this->mockQuery(1));
    $this->assertEquals(1, $p->total_pages);
  }

  // ---- current_page ----

  public function testCurrentPageDefaultsToOne(): void
  {
    $p = new Pagination($this->mockQuery(50));
    $this->assertSame(1, $p->current_page);
  }

  public function testCurrentPageIsSetExplicitly(): void
  {
    $p = new Pagination($this->mockQuery(50), 2);
    $this->assertSame(2, $p->current_page);
  }

  public function testCurrentPageClampedToOneWhenZeroPassed(): void
  {
    $p = new Pagination($this->mockQuery(50), 0);
    $this->assertSame(1, $p->current_page);
  }

  public function testCurrentPageClampedToTotalPagesWhenTooHigh(): void
  {
    // 30 records → 3 pages; requesting page 99 should clamp to 3
    $p = new Pagination($this->mockQuery(30), 99);
    $this->assertEquals(3, $p->current_page);
  }

  public function testCurrentPageClampedToOneOnEmptyResult(): void
  {
    $p = new Pagination($this->mockQuery(0), 5);
    $this->assertSame(1, $p->current_page);
  }

  // ---- previous_page ----

  public function testPreviousPageIsNullOnFirstPage(): void
  {
    $p = new Pagination($this->mockQuery(50), 1);
    $this->assertNull($p->previous_page);
  }

  public function testPreviousPageIsOneOnSecondPage(): void
  {
    $p = new Pagination($this->mockQuery(50), 2);
    $this->assertSame(1, $p->previous_page);
  }

  public function testPreviousPageIsCorrectOnLastPage(): void
  {
    $p = new Pagination($this->mockQuery(30), 3);
    $this->assertEquals(2, $p->previous_page);
  }

  // ---- next_page ----

  public function testNextPageIsNullOnLastPage(): void
  {
    $p = new Pagination($this->mockQuery(30), 3); // 3 pages total
    $this->assertNull($p->next_page);
  }

  public function testNextPageIsCorrectOnFirstPage(): void
  {
    $p = new Pagination($this->mockQuery(50), 1);
    $this->assertSame(2, $p->next_page);
  }

  public function testNextPageIsNullWhenOnlyOnePage(): void
  {
    $p = new Pagination($this->mockQuery(5), 1); // 5 records, 1 page
    $this->assertNull($p->next_page);
  }

  public function testNextPageIsNullWhenNoRecords(): void
  {
    $p = new Pagination($this->mockQuery(0), 1);
    $this->assertNull($p->next_page);
  }

  // ---- resources ----

  public function testResourcesIsCollection(): void
  {
    $p = new Pagination($this->mockQuery(10), 1);
    $this->assertInstanceOf(Collection::class, $p->resources);
  }

  // ---- start_id resolution ----

  public function testStartIdResolvesCorrectPage(): void
  {
    // 25 records across 3 pages (10/10/5).  id=11 is on page 2.
    $ids = range(1, 25);
    $mock = $this->mockQuery(25, $ids);
    $p = new Pagination($mock, null, 11);
    $this->assertSame(2, $p->current_page);
  }

  public function testStartIdResolvesPageOneForFirstRecord(): void
  {
    $ids = range(1, 20);
    $mock = $this->mockQuery(20, $ids);
    $p = new Pagination($mock, null, 1);
    $this->assertSame(1, $p->current_page);
  }

  public function testStartIdFallsBackToPageOneWhenNotFound(): void
  {
    $ids = [1, 2, 3];
    $mock = $this->mockQuery(3, $ids);
    $p = new Pagination($mock, null, 999);
    $this->assertSame(1, $p->current_page);
  }

  public function testStartIdAtExactPageBoundary(): void
  {
    // id=10 is the last record of page 1 (positions 1-10).  ceil(10/10) = 1.
    $ids = range(1, 20);
    $mock = $this->mockQuery(20, $ids);
    $p = new Pagination($mock, null, 10);
    $this->assertSame(1, $p->current_page);
  }

  public function testStartIdFirstRecordOfPageTwo(): void
  {
    // id=11 is position 11 → ceil(11/10) = 2
    $ids = range(1, 30);
    $mock = $this->mockQuery(30, $ids);
    $p = new Pagination($mock, null, 11);
    $this->assertSame(2, $p->current_page);
  }

  // ---- default per_page value ----

  public function testDefaultPerPageIsTen(): void
  {
    $p = new Pagination($this->mockQuery(0));
    $this->assertSame(10, $p->per_page);
  }
}
