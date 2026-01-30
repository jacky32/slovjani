<?php

/**
 * QueryBuilder - Chainable query builder similar to Rails ActiveRecord::Relation
 *
 * Usage:
 *   Post::where(['author_id' => 1])->where(['status' => 'published'])->get();
 *   Post::where(['author_id' => 1])->first();
 *   Post::where(['author_id' => 1])->count();
 *   Post::where('author_id', 1)->orderBy('created_at', 'DESC')->limit(10)->get();
 */
class QueryBuilder
{
  private string $modelClass;
  private array $conditions = [];
  private array $bindings = [];
  private ?string $orderByClause = null;
  private ?int $limitValue = null;
  private ?int $offsetValue = null;

  public function __construct(string $modelClass)
  {
    $this->modelClass = $modelClass;
  }

  /**
   * Add WHERE conditions
   *
   * @param array|string $conditions Hash of conditions or column name
   * @param mixed $value Value when using column name format
   * @return self
   */
  public function where($conditions, $value = null): self
  {
    // Support: where('column', 'value') format
    if (is_string($conditions) && $value !== null) {
      $conditions = [$conditions => $value];
    }

    foreach ($conditions as $column => $val) {
      if (is_array($val)) {
        // Support: where(['status' => ['active', 'pending']]) for IN clause
        $placeholders = array_fill(0, count($val), '?');
        $this->conditions[] = "`{$column}` IN (" . implode(', ', $placeholders) . ")";
        $this->bindings = array_merge($this->bindings, $val);
      } elseif ($val === null) {
        $this->conditions[] = "`{$column}` IS NULL";
      } else {
        $this->conditions[] = "`{$column}` = ?";
        $this->bindings[] = $val;
      }
    }

    return $this;
  }

  /**
   * Add WHERE NOT conditions
   */
  public function whereNot($conditions, $value = null): self
  {
    if (is_string($conditions) && $value !== null) {
      $conditions = [$conditions => $value];
    }

    foreach ($conditions as $column => $val) {
      if ($val === null) {
        $this->conditions[] = "`{$column}` IS NOT NULL";
      } else {
        $this->conditions[] = "`{$column}` != ?";
        $this->bindings[] = $val;
      }
    }

    return $this;
  }

  /**
   * Add ORDER BY clause
   */
  public function orderBy(string $column, string $direction = 'ASC'): self
  {
    $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
    $this->orderByClause = "`{$column}` {$direction}";
    return $this;
  }

  /**
   * Add LIMIT clause
   */
  public function limit(int $limit): self
  {
    $this->limitValue = $limit;
    return $this;
  }

  /**
   * Add OFFSET clause
   */
  public function offset(int $offset): self
  {
    $this->offsetValue = $offset;
    return $this;
  }

  /**
   * Execute query and return all results as Collection
   */
  public function get(): Collection
  {
    return new Collection($this->executeQuery());
  }

  /**
   * Alias for get() - more Rails-like
   */
  public function all(): Collection
  {
    return $this->get();
  }

  /**
   * Get the first result or null
   */
  public function first(): ?object
  {
    $this->limitValue = 1;
    $results = $this->executeQuery();
    return $results[0] ?? null;
  }

  /**
   * Get count of matching records
   */
  public function count(): int
  {
    $sql = $this->buildCountSql();
    Logger::sql($sql, $this->bindings);

    $database = new Database();
    $connection = $database->getConnection();
    $stmt = $connection->prepare($sql);

    if (!empty($this->bindings)) {
      $types = $this->getBindingTypes();
      $stmt->bind_param($types, ...$this->bindings);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return (int) ($row['count'] ?? 0);
  }

  /**
   * Check if any records exist
   */
  public function exists(): bool
  {
    return $this->count() > 0;
  }

  /**
   * Build and execute the SELECT query
   */
  private function executeQuery(): array
  {
    $sql = $this->buildSql();
    Logger::sql($sql, $this->bindings);

    $database = new Database();
    $connection = $database->getConnection();
    $stmt = $connection->prepare($sql);

    if (!empty($this->bindings)) {
      $types = $this->getBindingTypes();
      $stmt->bind_param($types, ...$this->bindings);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    $modelClass = $this->modelClass;
    $dbAttributes = $modelClass::getDbAttributes();

    while ($row = $result->fetch_assoc()) {
      $attributes = [];
      foreach ($dbAttributes as $attr) {
        $attributes[$attr] = $row[$attr] ?? null;
      }
      $results[] = new $modelClass($attributes);
    }

    return $results;
  }

  /**
   * Build the SQL query string
   */
  private function buildSql(): string
  {
    $table = toSnakeCase($this->modelClass) . 's';
    $sql = "SELECT * FROM `{$table}`";

    if (!empty($this->conditions)) {
      $sql .= " WHERE " . implode(" AND ", $this->conditions);
    }

    if ($this->orderByClause) {
      $sql .= " ORDER BY " . $this->orderByClause;
    }

    if ($this->limitValue !== null) {
      $sql .= " LIMIT " . $this->limitValue;
    }

    if ($this->offsetValue !== null) {
      $sql .= " OFFSET " . $this->offsetValue;
    }

    return $sql . ";";
  }

  /**
   * Build COUNT query
   */
  private function buildCountSql(): string
  {
    $table = toSnakeCase($this->modelClass) . 's';
    $sql = "SELECT COUNT(*) as count FROM `{$table}`";

    if (!empty($this->conditions)) {
      $sql .= " WHERE " . implode(" AND ", $this->conditions);
    }

    return $sql . ";";
  }

  /**
   * Get mysqli binding types string
   */
  private function getBindingTypes(): string
  {
    $types = '';
    foreach ($this->bindings as $binding) {
      if (is_int($binding)) {
        $types .= 'i';
      } elseif (is_float($binding)) {
        $types .= 'd';
      } else {
        $types .= 's';
      }
    }
    return $types;
  }
}
