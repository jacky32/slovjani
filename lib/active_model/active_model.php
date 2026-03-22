<?php
require_once __DIR__ . '/validations/validations.php';
require_once __DIR__ . '/relations/pagination.php';
require_once __DIR__ . '/relations/query_builder.php';
require_once __DIR__ . '/relations/collection.php';
require_once __DIR__ . '/relations/relations.php';
require_once __DIR__ . '/attributes/attributes.php';

use ActiveModel\Validations;

/**
 * Lightweight Active Record base class with attributes, validations, and relations.
 */
abstract class ActiveModel
{
  use Validations;
  use Relations;
  use Attributes;

  protected $db;
  protected $connection;

  protected static array $db_attributes = [];
  protected static array $validations = [];
  protected static array $validation_callbacks = [];

  /**
   * Returns the list of database column names defined for this model class.
   *
   * @return array<string> Array of column name strings.
   */
  public static function getDbAttributes(): array
  {
    return static::$db_attributes;
  }

  /**
   * Opens a database connection and initializes attribute storage.
   *
   * @param array $data Associative array of attribute values to pre-populate.
   */
  public function __construct($data = [])
  {
    $this->db = new Database();
    $this->connection = $this->db->getConnection();

    $this->initializeAttributes($data);
  }

  /**
   * Magic getter: resolves a named attribute first, then a named relation.
   *
   * @param string $name Property name.
   * @return mixed The attribute value or related object/QueryBuilder, or null.
   */
  public function __get($name)
  {
    // Try to get attribute first
    $attributeValue = $this->getAttribute($name);
    if ($attributeValue !== null || in_array($name, static::$db_attributes ?? [])) {
      return $attributeValue;
    }

    // Try to get relation
    return $this->getRelation($name);
  }

  /**
   * Magic setter: stores a value in the attribute store.
   *
   * @param string $name  Attribute name.
   * @param mixed  $value The value to store.
   * @return void
   */
  public function __set($name, $value)
  {
    $this->setAttribute($name, $value);
  }

  /**
   * Magic isset check: returns true if $name is a known attribute or relation.
   *
   * @param string $name Property name.
   * @return bool
   */
  public function __isset($name)
  {
    return $this->hasAttribute($name) || $this->hasRelation($name);
  }

  /**
   * Closes the database connection when the object is garbage-collected.
   */
  public function __destruct()
  {
    $this->closeConnection();
  }


  /**
   * Returns the raw mysqli connection held by this instance.
   *
   * @return \mysqli
   */
  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Closes the active mysqli connection if it is open.
   *
   * @return void
   */
  public function closeConnection()
  {
    if ($this->connection) {
      $this->connection->close();
    }
  }

  /**
   * Returns the human-readable translation of an attribute name.
   *
   * @param string $attribute The attribute name to translate.
   * @return string The translated label from the locale file.
   */
  public static function humanAttributeName($attribute)
  {
    return t("attributes." . toSnakeCase(static::class) . "." . $attribute);
  }

  /**
   * Validates the record and either creates a new row or updates the existing one.
   *
   * @return void
   * @throws \ActiveModel\ValidationException If validation fails.
   */
  function save()
  {
    $this->validate();
    if (isset($this->id)) {
      $this->update();
    } else {
      $this->create();
    }
  }

  /**
   * Runs all configured validations and collects any violations.
   *
   * @return void
   * @throws \ActiveModel\ValidationException If one or more validations fail.
   */
  function validate()
  {
    $caught_exceptions = [];
    foreach (static::$validations as $validation_type => $attributes) {
      switch ($validation_type) {
        case "presence":
          $caught_exceptions = array_merge($caught_exceptions, $this->validates_presence_of($attributes));
          break;
        case "inclusion":
          $caught_exceptions = array_merge($caught_exceptions, $this->validates_inclusion_of($attributes));
          break;
        case "uniqueness":
          $caught_exceptions = array_merge($caught_exceptions, $this->validates_uniqueness_of($attributes));
          break;
        case "length":
          $caught_exceptions = array_merge($caught_exceptions, $this->validates_length_of($attributes));
          break;
      }
    }
    foreach (static::$validation_callbacks as $callback) {
      if (!method_exists($this, $callback)) {
        throw new \LogicException("Validation callback '{$callback}' is not defined on " . static::class . ".");
      }

      $callback_result = $this->{$callback}();
      if (!is_array($callback_result)) {
        throw new \LogicException("Validation callback '{$callback}' on " . static::class . " must return an array of validation exceptions.");
      }

      $caught_exceptions = array_merge($caught_exceptions, $callback_result);
    }
    if (!empty($caught_exceptions)) {
      throw new \ActiveModel\ValidationException(t("errors.validation_failed"), 0, null, $caught_exceptions);
    }
  }


  /**
   * Inserts a new row into the corresponding database table and populates $this->id.
   *
   * @return void
   */
  function create()
  {
    $columns = [];
    $placeholders = [];
    $values = [];
    $types = '';

    foreach (static::$db_attributes as $attribute) {
      if ($attribute !== 'id') {
        if ($attribute === 'created_at' || $attribute === 'updated_at') {
          $this->{$attribute} = date('Y-m-d H:i:s');
        }
        $columns[] = "`{$attribute}`";
        $placeholders[] = '?';
        $values[] = $this->{$attribute};
        $types .= $this->getBindingType($this->{$attribute});
      }
    }

    $table = toSnakeCase(static::class) . 's';
    $sql = "INSERT INTO `{$table}` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
    Logger::sql($sql, $values);

    $stmt = $this->connection->prepare($sql);
    if (!empty($values)) {
      $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();
    $this->id = $this->connection->insert_id;
  }

  /**
   * Updates the existing database row for this record.
   *
   * @return void
   */
  function update()
  {
    $setParts = [];
    $values = [];
    $types = '';

    foreach (static::$db_attributes as $attribute) {
      if ($attribute !== 'id') {
        if ($attribute === 'updated_at') {
          $this->{$attribute} = date('Y-m-d H:i:s');
        }
        $setParts[] = "`{$attribute}` = ?";
        $values[] = $this->{$attribute};
        $types .= $this->getBindingType($this->{$attribute});
      }
    }

    // Add id for WHERE clause
    $values[] = $this->id;
    $types .= 'i';

    $table = toSnakeCase(static::class) . 's';
    $sql = "UPDATE `{$table}` SET " . implode(", ", $setParts) . " WHERE id = ?";
    Logger::sql($sql, $values);

    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
  }

  /**
   * Deletes the database row corresponding to this record.
   *
   * @return void
   */
  function destroy()
  {
    $table = toSnakeCase(static::class) . 's';
    $sql = "DELETE FROM `{$table}` WHERE id = ?";
    Logger::sql($sql, [$this->id]);

    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param('i', $this->id);
    $stmt->execute();
  }

  /**
   * Returns the mysqli binding type character for a given value.
   *
   * @param mixed $value The value to inspect.
   * @return string 'i' for integer, 'd' for float, 's' for string/null.
   */
  private function getBindingType($value): string
  {
    if (is_int($value)) {
      return 'i';
    } elseif (is_float($value)) {
      return 'd';
    } elseif (is_null($value)) {
      return 's';
    } else {
      return 's';
    }
  }

  /**
   * Finds a record by its primary key ID.
   *
   * @param int|string $id The ID of the record to find.
   * @return static|null The found record as an instance of the model, or null if not found.
   */
  public static function find($id)
  {
    // Accept integer values or integer strings (e.g. "1" -> 1).
    // Reject non-integer strings such as "true", floats or empty values.
    if (is_int($id)) {
      // ok
    } elseif (is_string($id) && ctype_digit($id)) {
      $id = (int) $id;
    } else {
      return null;
    }

    $table = toSnakeCase(static::class) . 's';
    $sql = "SELECT * FROM `{$table}` WHERE id = ?";
    Logger::sql($sql, [$id]);

    $database = new Database();
    $connection = $database->getConnection();
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      $attributes = [];
      foreach (static::$db_attributes as $attr) {
        $attributes[$attr] = $row[$attr] ?? null;
      }
      return new static($attributes);
    } else {
      return null;
    }
  }

  /**
   * Chainable where query - returns QueryBuilder
   *
   * Usage:
   *   Post::where(['author_id' => 1])->get();
   *   Post::where('status', 'active')->first();
   *   Post::where(['author_id' => 1])->where(['status' => 'active'])->orderBy('created_at', 'DESC')->get();
   */
  public static function where($conditions = [], $value = null): QueryBuilder
  {
    $builder = new QueryBuilder(static::class);
    return $builder->where($conditions, $value);
  }

  /**
   * Chainable orderBy query - returns QueryBuilder
   */
  public static function orderBy(string $column, string $direction = 'ASC'): QueryBuilder
  {
    $builder = new QueryBuilder(static::class);
    return $builder->orderBy($column, $direction);
  }

  /**
   * Paginate results - returns Pagination object with metadata and current page resources
   *
   * Usage:
   *   $pagination = Post::paginate(2, 10); // Get page 2 with 10 posts per page
   *   $pagination->resources; // Array of Post objects for current page
   *   $pagination->current_page; // Current page number
   *   $pagination->total_pages; // Total number of pages
   *   $pagination->previous_page; // Previous page number or null if on first page
   *   $pagination->next_page; // Next page number or null if on last page
   */
  public static function paginate(int|null $current_page = 1, int|null $start_id = null): Pagination
  {
    $builder = new QueryBuilder(static::class);
    return $builder->paginate($current_page, $start_id);
  }

  /**
   * Return all records as Collection
   */
  public static function all(): Collection
  {
    $results = [];
    $sql = "SELECT * FROM " . toSnakeCase(static::class) . "s";
    Logger::sql($sql);
    $database = new Database();
    $connection = $database->getConnection();
    $result = $connection->query($sql);

    while ($row = $result->fetch_assoc()) {
      $attributes = [];
      foreach (static::$db_attributes as $attr) {
        $attributes[$attr] = $row[$attr] ?? null;
      }
      $results[] = new static($attributes);
    }

    return new Collection($results);
  }

  /**
   * Returns true if a record exists with the given conditions, false otherwise.
   *
   */
  public static function exists(array $conditions = []): bool
  {
    $table = toSnakeCase(static::class) . 's';
    $sql = "SELECT 1 FROM `{$table}` LIMIT 1";
    $params = [];

    if (!empty($conditions)) {
      $whereParts = [];
      foreach ($conditions as $column => $value) {
        $whereParts[] = "`{$column}` = ?";
        $params[] = $value;
      }
      $sql .= " WHERE " . implode(" AND ", $whereParts);
    }

    Logger::sql($sql, $params);
    $database = new Database();
    $connection = $database->getConnection();
    $stmt = $connection->prepare($sql);
    if (!empty($params)) {
      $types = str_repeat('s', count($params));
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      return (int) $row['1'] > 0;
    }

    return false;
  }
}
