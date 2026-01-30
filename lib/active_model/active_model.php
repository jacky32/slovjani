<?php
require __DIR__ . '/validations/validations.php';
require __DIR__ . '/relations/query_builder.php';
require __DIR__ . '/relations/collection.php';

use ActiveModel\Validations;

abstract class ActiveModel
{
  use Validations;

  protected $db;
  protected $connection;

  protected $id;
  protected $attributes = [];

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

    foreach (static::$db_attributes as $attribute) {
      if (isset($data[$attribute])) {
        $this->$attribute = $data[$attribute];
      }
    }
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
      // error_log("Validating {$validation_type} for attributes: " . json_encode($attributes));
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
    foreach (static::$db_attributes as $attribute) {
      if ($attribute != 'id') {
        if ($attribute == 'created_at' || $attribute == 'updated_at') {
          $this->{$attribute} = date('Y-m-d H:i:s');
        }
        $attributes_parts[] = $attribute . " = '" . $this->{$attribute} . "'";
      }
    }

    $sql = "INSERT INTO " . toSnakeCase(static::class) . "s (" . implode(", ", array_filter(static::$db_attributes, fn($attr) => $attr !== 'id')) . ") VALUES" .
      " (" . implode(", ", array_map(fn($attr) => "'" . $this->{$attr} . "'", array_filter(static::$db_attributes, fn($attr) => $attr !== 'id'))) . ");";
    // ('" . $this->name . "', '" . $this->body . "', '" . $this->creator_id . "');";
    error_log("[MySQL] " . $sql);
    $this->connection->query($sql);
    $this->id = $this->connection->insert_id;
  }

  function update()
  {
    foreach (static::$db_attributes as $attribute) {
      if ($attribute != 'id') {
        if ($attribute == 'updated_at') {
          $this->{$attribute} = date('Y-m-d H:i:s');
        }
        $attributes_parts[] = $attribute . " = '" . $this->{$attribute} . "'";
      }
    }

    $sql = "UPDATE " . toSnakeCase(static::class) . "s SET " . implode(", ", $attributes_parts) . " WHERE id = " . $this->id . ";";
    error_log("[MySQL] " . $sql);
    $this->connection->query($sql);
  }

  function destroy()
  {
    $sql = "DELETE FROM " . toSnakeCase(static::class) . "s WHERE id = " . $this->id . ";";
    error_log("[MySQL] " . $sql);
    $this->connection->query($sql);
  }


  // public static function all()
  // {
  //   $results = [];
  //   $sql = "SELECT * FROM " . toSnakeCase(static::class) . "s;";
  //   error_log("[MySQL] " . $sql);
  //   $database = new Database();
  //   $connection = $database->getConnection();
  //   $result = $connection->query($sql);

  //   while ($row = $result->fetch_assoc()) {
  //     $attributes = [];
  //     foreach (static::$db_attributes as $attr) {
  //       $attributes[$attr] = $row[$attr] ?? null;
  //     }
  //     $results[] = new static($attributes);
  //   }

  //   return $results;
  // }



  public static function find($id)
  {
    if (empty($id) || !is_numeric($id)) {
      return null;
    }
    $sql = "SELECT * FROM " . toSnakeCase(static::class) . "s WHERE id = " . $id . ";";
    error_log("[MySQL] " . $sql);
    $database = new Database();
    $connection = $database->getConnection();
    $result = $connection->query($sql);

    if ($row = $result->fetch_assoc()) {
      $attributes = [];
      foreach (static::$db_attributes as $attr) {
        $attributes[$attr] = $row[$attr] ?? null;
      }
      $post = new static($attributes);
      return $post;
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
    $sql = "SELECT * FROM " . toSnakeCase(static::class) . "s;";
    error_log("[MySQL] " . $sql);
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
