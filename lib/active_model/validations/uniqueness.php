<?php

namespace ActiveModel\Validations;

trait UniquenessValidator
{
  // 'uniqueness' => [['user_id', 'question_id']] -- composite unique key
  // or 'uniqueness' => ['email', 'user_id']      -- single unique keys
  public function validates_uniqueness_of(array $attributes)
  {
    $caught_exceptions = [];
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
      $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$condition_str} AND id != " . ($this->id ?? "NULL");
      \Logger::sql($query);
      $database = new \Database();
      $connection = $database->getConnection();
      $result = $connection->query($query);
      $row = $result->fetch_assoc();
      if ($row['count'] > 0) {
        if (is_array($attribute)) {
          $attr_names = implode(", ", $attribute);
          foreach ($attribute as $attr) {
            $caught_exceptions[] = [
              'class' => static::class,
              'attribute' => $attr,
              'message' => t("errors.combination_must_be_unique", ['attributes' => $attr_names])
            ];
          }
        } else {
          $caught_exceptions[] = [
            'class' => static::class,
            'attribute' => $attribute,
            'message' => t("errors.must_be_unique")
          ];
        }
      }
    }
    return $caught_exceptions;
  }
}
