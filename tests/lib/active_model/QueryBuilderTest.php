<?php

declare(strict_types=1);


use PHPUnit\Framework\TestCase;

/**
 * Tests for QueryBuilder.
 *
 * Methods that execute SQL (get, first, count, find with valid id, pluck, exists)
 * require a live database connection and are excluded here.
 *
 * Covered:
 *   - Chainable builder methods return $this (where, whereNot, orderBy, limit, offset).
 *   - find() returns null immediately for non-numeric inputs (no DB call).
 *   - Private SQL-building helpers verified via Reflection.
 */
final class QueryBuilderTest extends TestCase
{
  private function builder(string $model = 'Post'): QueryBuilder
  {
    return new QueryBuilder($model);
  }

  /** Invoke a private method on $object via Reflection. */
  private function callPrivate(object $object, string $method, array $args = []): mixed
  {
    $ref = new ReflectionMethod($object, $method);
    $ref->setAccessible(true);
    return $ref->invoke($object, ...$args);
  }

  // ---- Chainable methods return self ----

  public function testWhereReturnsSelf(): void
  {
    $qb = $this->builder();
    $this->assertSame($qb, $qb->where(['status' => 'active']));
  }

  public function testWhereWithColumnValueFormatReturnsSelf(): void
  {
    $qb = $this->builder();
    $this->assertSame($qb, $qb->where('status', 'active'));
  }

  public function testWhereNotReturnsSelf(): void
  {
    $qb = $this->builder();
    $this->assertSame($qb, $qb->whereNot(['deleted' => true]));
  }

  public function testOrderByReturnsSelf(): void
  {
    $qb = $this->builder();
    $this->assertSame($qb, $qb->orderBy('created_at', 'DESC'));
  }

  public function testLimitReturnsSelf(): void
  {
    $qb = $this->builder();
    $this->assertSame($qb, $qb->limit(10));
  }

  public function testOffsetReturnsSelf(): void
  {
    $qb = $this->builder();
    $this->assertSame($qb, $qb->offset(20));
  }

  public function testChainingMultipleCallsReturnsSelf(): void
  {
    $qb = $this->builder();
    $result = $qb->where(['active' => 1])->orderBy('id')->limit(5)->offset(10);
    $this->assertSame($qb, $result);
  }

  // ---- find() returns null for invalid inputs (no DB call) ----

  public function testFindWithNonNumericStringReturnsNull(): void
  {
    $this->assertNull($this->builder()->find('abc'));
  }

  public function testFindWithEmptyStringReturnsNull(): void
  {
    $this->assertNull($this->builder()->find(''));
  }

  public function testFindWithFloatStringReturnsNull(): void
  {
    // ctype_digit('1.5') === false → returns null
    $this->assertNull($this->builder()->find('1.5'));
  }

  public function testFindWithNullReturnsNull(): void
  {
    $this->assertNull($this->builder()->find(null));
  }

  public function testFindWithArrayReturnsNull(): void
  {
    $this->assertNull($this->builder()->find([]));
  }

  // ---- buildSql() — tested via Reflection ----

  public function testBuildSqlBasicSelect(): void
  {
    $sql = $this->callPrivate($this->builder('Post'), 'buildSql');
    $this->assertSame('SELECT * FROM `posts`;', $sql);
  }

  public function testBuildSqlUsesModelClassForTableName(): void
  {
    $sql = $this->callPrivate($this->builder('Event'), 'buildSql');
    $this->assertSame('SELECT * FROM `events`;', $sql);
  }

  public function testBuildSqlIncludesWhereClause(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['status' => 'published']);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('WHERE', $sql);
    $this->assertStringContainsString('`status` = ?', $sql);
  }

  public function testBuildSqlIncludesMultipleConditions(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['status' => 'published'])->where(['author_id' => 1]);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('`status` = ?', $sql);
    $this->assertStringContainsString('`author_id` = ?', $sql);
    $this->assertStringContainsString(' AND ', $sql);
  }

  public function testBuildSqlWithNullConditionUsesIsNull(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['deleted_at' => null]);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('`deleted_at` IS NULL', $sql);
  }

  public function testBuildSqlWithWhereNotConditionUsesNotEquals(): void
  {
    $qb = $this->builder('Post');
    $qb->whereNot(['status' => 'draft']);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('`status` != ?', $sql);
  }

  public function testBuildSqlWithWhereNotNullConditionUsesIsNotNull(): void
  {
    $qb = $this->builder('Post');
    $qb->whereNot(['deleted_at' => null]);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('`deleted_at` IS NOT NULL', $sql);
  }

  public function testBuildSqlWithInArrayCondition(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['status' => ['active', 'pending']]);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('`status` IN (?, ?)', $sql);
  }

  public function testBuildSqlIncludesOrderBy(): void
  {
    $qb = $this->builder('Post');
    $qb->orderBy('created_at', 'DESC');
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('ORDER BY `created_at` DESC', $sql);
  }

  public function testBuildSqlOrderByDefaultsToAsc(): void
  {
    $qb = $this->builder('Post');
    $qb->orderBy('id');
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('ORDER BY `id` ASC', $sql);
  }

  public function testBuildSqlOrderByRejectsInvalidDirection(): void
  {
    // An invalid direction string should be coerced to ASC
    $qb = $this->builder('Post');
    $qb->orderBy('id', 'INVALID');
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('ORDER BY `id` ASC', $sql);
  }

  public function testBuildSqlIncludesLimit(): void
  {
    $qb = $this->builder('Post');
    $qb->limit(10);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('LIMIT 10', $sql);
  }

  public function testBuildSqlIncludesOffset(): void
  {
    $qb = $this->builder('Post');
    $qb->offset(20);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('OFFSET 20', $sql);
  }

  public function testBuildSqlLimitAndOffsetTogether(): void
  {
    $qb = $this->builder('Post');
    $qb->limit(10)->offset(30);
    $sql = $this->callPrivate($qb, 'buildSql');
    $this->assertStringContainsString('LIMIT 10', $sql);
    $this->assertStringContainsString('OFFSET 30', $sql);
  }

  public function testBuildSqlFullQueryOrdering(): void
  {
    // SELECT * FROM `posts` WHERE ... ORDER BY ... LIMIT ... OFFSET ...;
    $qb = $this->builder('Post');
    $qb->where(['status' => 'active'])->orderBy('created_at', 'DESC')->limit(5)->offset(10);
    $sql = $this->callPrivate($qb, 'buildSql');
    $wherePos  = strpos($sql, 'WHERE');
    $orderPos  = strpos($sql, 'ORDER');
    $limitPos  = strpos($sql, 'LIMIT');
    $offsetPos = strpos($sql, 'OFFSET');
    $this->assertLessThan($orderPos, $wherePos);
    $this->assertLessThan($limitPos,  $orderPos);
    $this->assertLessThan($offsetPos, $limitPos);
  }

  // ---- buildCountSql() — tested via Reflection ----

  public function testBuildCountSqlBasic(): void
  {
    $sql = $this->callPrivate($this->builder('Post'), 'buildCountSql');
    $this->assertSame('SELECT COUNT(*) as count FROM `posts`;', $sql);
  }

  public function testBuildCountSqlIncludesWhereClause(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['status' => 'active']);
    $sql = $this->callPrivate($qb, 'buildCountSql');
    $this->assertStringContainsString('COUNT(*) as count', $sql);
    $this->assertStringContainsString('WHERE', $sql);
    $this->assertStringContainsString('`status` = ?', $sql);
  }

  public function testBuildCountSqlDoesNotIncludeLimitOrOffset(): void
  {
    $qb = $this->builder('Post');
    $qb->limit(10)->offset(5);
    $sql = $this->callPrivate($qb, 'buildCountSql');
    $this->assertStringNotContainsString('LIMIT', $sql);
    $this->assertStringNotContainsString('OFFSET', $sql);
  }

  // ---- getBindingTypes() — tested via Reflection ----

  public function testGetBindingTypesForInteger(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['id' => 1]);
    $types = $this->callPrivate($qb, 'getBindingTypes');
    $this->assertSame('i', $types);
  }

  public function testGetBindingTypesForString(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['status' => 'active']);
    $types = $this->callPrivate($qb, 'getBindingTypes');
    $this->assertSame('s', $types);
  }

  public function testGetBindingTypesForFloat(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['score' => 3.14]);
    $types = $this->callPrivate($qb, 'getBindingTypes');
    $this->assertSame('d', $types);
  }

  public function testGetBindingTypesForMixedTypes(): void
  {
    $qb = $this->builder('Post');
    $qb->where(['id' => 1])->where(['status' => 'active'])->where(['score' => 9.5]);
    $types = $this->callPrivate($qb, 'getBindingTypes');
    $this->assertSame('isd', $types);
  }

  public function testGetBindingTypesEmptyWhenNoConditions(): void
  {
    $qb = $this->builder('Post');
    $types = $this->callPrivate($qb, 'getBindingTypes');
    $this->assertSame('', $types);
  }
}
