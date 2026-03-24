<?php

namespace ActiveModel\Validations;

/**
 * Trait providing uniqueness validation against persisted records.
 */
trait UniquenessValidator
{
  /**
   * Validates that the specified attribute(s) are unique in the database. For each attribute or combination of attributes,
   * it constructs a SQL query to count how many records exist with the same value(s).
   * If the count is greater than 0, it means there is a violation of uniqueness, and an appropriate error message is added to the list of caught exceptions.
   * The method handles both single attribute uniqueness and composite key uniqueness. For composite keys,
   * it generates a condition that checks for the combination of attributes. It also ensures that when updating an existing record
   * it does not count the record itself as a violation by adding a condition to exclude the current record based on its primary key(s).
   *
   * @param array $attributes An array of attribute names or an array of arrays for composite keys that should be validated for uniqueness.
   * Each element can be a string (for single attribute uniqueness) or an array of strings (for composite key uniqueness).
   * For example:
   * - ['email'] to validate that the email attribute is unique.
   * - [['user_id', 'question_id']] to validate that the combination of user_id and question_id is unique.
   * - ['email', 'username'] to validate that both email and username are unique individually.
   * @return array An array of caught exceptions, where each exception is an associative array containing the class name, attribute name(s), and error message for each uniqueness violation found during validation.
   */
  public function validates_uniqueness_of(array $attributes)
  {
    $caught_exceptions = [];
    foreach ($attributes as $attribute) {
      $table = toSnakeCase((new \ReflectionClass($this))->getShortName()) . "s";

      $query = "SELECT 1 FROM {$table} WHERE {$this->getConditionStringFromAttributes($attribute)}" . $this->getIdCondition() . " LIMIT 1";
      \Logger::sql($query);
      $database = new \Database();
      $connection = $database->getConnection();
      $result = $connection->query($query);
      if ($result === false) {
        continue;
      }

      $row = $result->fetch_assoc();
      $this->addViolationToExceptions($caught_exceptions, $attribute, $row);
    }
    return $caught_exceptions;
  }

  /**
   * Adds validation violation to the list of caught exceptions if the count from the uniqueness query is greater than 0.
   * If the attribute is an array, it assumes a composite unique key and adds a message indicating that the combination of attributes must be unique. If it's a single attribute, it adds a message indicating that the attribute must be unique.
   * @param array $caught_exceptions Reference to the array where caught exceptions are stored. This array will be modified if a uniqueness violation is found.
   * @param array|string $attribute The attribute(s) that were validated for uniqueness. Can be a single attribute name or an array of attribute names for composite keys.
   * @param array|null $row The result row from the uniqueness query, which contains the count of records that match the uniqueness condition. If the count is greater than 0, it indicates a violation of uniqueness.
   */
  private function addViolationToExceptions(array &$caught_exceptions, array|string $attribute, ?array $row): void
  {
    if ($row === null) {
      return;
    }

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

  /**
   * Generates SQL condition string based on the provided attributes for uniqueness validation.
   * If the attributes are an array, it assumes a composite unique key and generates conditions for each attribute.
   * If it's a single attribute, it generates a simple condition.
   * @param array|string $attribute The attribute(s) to generate the condition for. Can be a single attribute name or an array of attribute names for composite keys.
   * @return String SQL condition string to be used in the uniqueness validation query.
   */
  private function getConditionStringFromAttributes(array|string $attribute): String
  {
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
    return $condition_str;
  }

  /**
   * Generates SQL condition to exclude current record based on primary key(s) when checking for uniqueness.
   * If the record has an 'id' attribute, it will exclude that id. If the model defines a composite primary key,
   * it will exclude the combination of those keys.
   * This ensures that when updating an existing record, it won't fail the uniqueness validation against itself.
   * @return String SQL condition to exclude current record from uniqueness check.
   */
  private function getIdCondition(): String
  {
    $id_condition = "";
    if (isset($this->id)) {
      $id_condition = " AND id != " . $this->id;
    } else if (isset(static::$composite_primary_key)) {
      $composite_primary_key = static::$composite_primary_key;
      $conditions = [];
      foreach ($composite_primary_key as $key) {
        $conditions[] = "{$key} != '" . $this->{$key} . "'";
      }
      $id_condition .= " AND (" . implode(" AND ", $conditions) . ")";
    }
    return $id_condition;
  }
}
