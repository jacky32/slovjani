<?php
require __DIR__ . '/validations/validations.php';
require __DIR__ . '/relations/query_builder.php';
require __DIR__ . '/relations/collection.php';
require __DIR__ . '/relations/relations.php';
require __DIR__ . '/attributes/attributes.php';

use ActiveModel\Validations;

abstract class ActiveModel
{
  use Validations;
  use Relations;
  use Attributes;

  protected $db;
  protected $connection;

  protected static array $db_attributes = [];
  protected static array $validations = [];

  /**
   * Get db_attributes for QueryBuilder
   */
  public static function getDbAttributes(): array
  {
    return static::$db_attributes;
  }

  public function __construct($data = [])
  {
    $this->db = new Database();
    $this->connection = $this->db->getConnection();

    $this->initializeAttributes($data);
  }

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

  public function __set($name, $value)
  {
    $this->setAttribute($name, $value);
  }

  public function __isset($name)
  {
    return $this->hasAttribute($name) || $this->hasRelation($name);
  }

  public function __destruct()
  {
    $this->closeConnection();
  }


  public function getConnection()
  {
    return $this->connection;
  }

  public function closeConnection()
  {
    if ($this->connection) {
      $this->connection->close();
    }
  }

  public static function humanAttributeName($attribute)
  {
    return t("attributes." . toSnakeCase(static::class) . "." . $attribute);
  }

  function save()
  {
    $this->validate();
    if (isset($this->id)) {
      $this->update();
    } else {
      $this->create();
    }
  }

  function validate()
  {
    foreach (static::$validations as $validation_type => $attributes) {
      switch ($validation_type) {
        case "presence":
          $this->validates_presence_of($attributes);
          break;
        case "inclusion":
          $this->validates_inclusion_of($attributes);
          break;
        case "uniqueness":
          $this->validates_uniqueness_of($attributes);
          break;
        case "length":
          $this->validates_length_of($attributes);
          break;
      }
    }
  }


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
   * Get mysqli binding type for a value
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
}
