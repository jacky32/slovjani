<?php

namespace ActiveModel\Validations;

trait UniquenessValidator
{
  // 'uniqueness' => [['user_id', 'question_id']] -- composite unique key
  // or 'uniqueness' => ['email', 'user_id']      -- single unique keys
  public function validates_uniqueness_of(array $attributes)
  {
    foreach ($attributes as $attribute) {
      $condition_str = "";
      if (is_array($attribute)) {
        // Composite unique key
        $conditions = [];
        foreach ($attribute as $attr) {
          $conditions[] = "{$attr} = '" . $this->{$attr} . "'";
        }
        $condition_str = implode(" AND ", $conditions);
      } else {
        // Single unique key
        $condition_str = "{$attribute} = '" . $this->{$attribute} . "'";
      }
      $table = toSnakeCase((new \ReflectionClass($this))->getShortName()) . "s";
      $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$condition_str}";
      error_log("[MySQL] " . $query);
      $database = new \Database();
      $connection = $database->getConnection();
      $result = $connection->query($query);
      $row = $result->fetch_assoc();
      if ($row['count'] > 0) {
        if (is_array($attribute)) {
          $attr_names = implode(", ", $attribute);
          throw new \Exception("Combination of {$attr_names} must be unique");
        } else {
          throw new \Exception("{$attribute} must be unique");
        }
      }
    }
  }
}
