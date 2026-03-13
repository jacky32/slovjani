<?php

/**
 * Trait that resolves and caches belongs_to and has_many associations.
 */
trait Relations
{
  private array $_loaded_relations = [];

  /**
   * Get a relation by name
   */
  protected function getRelation($name)
  {
    // Check if it's a belongs_to relation
    $belongs_to = static::$relations['belongs_to'] ?? [];
    if (isset($belongs_to[$name])) {
      if (isset($belongs_to[$name]['polymorphic']) && $belongs_to[$name]['polymorphic']) {
        if (!isset($this->_loaded_relations[$name])) {
          $foreign_key = $belongs_to[$name]['foreign_key'];
          $foreign_type = $belongs_to[$name]['foreign_type'];
          $class_name = $this->{$foreign_type};
          $this->_loaded_relations[$name] = $class_name::find($this->{$foreign_key});
        }
        return $this->_loaded_relations[$name];
      }
      if (!isset($this->_loaded_relations[$name])) {
        $foreign_key = $belongs_to[$name]['foreign_key'];
        $class_name = $belongs_to[$name]['class_name'];
        $this->_loaded_relations[$name] = $class_name::find($this->{$foreign_key});
      }
      return $this->_loaded_relations[$name];
    }

    // Check if it's a has_many relation
    $has_many = static::$relations['has_many'] ?? [];
    if (isset($has_many[$name])) {
      if (!isset($this->_loaded_relations[$name])) {
        $this->_loaded_relations[$name] = $this->setHasManyRelation($has_many[$name]);
      }
      return $this->_loaded_relations[$name];
    }

    return null;
  }

  /**
   * Check if a relation exists
   */
  protected function hasRelation($name)
  {
    $belongs_to = static::$relations['belongs_to'] ?? [];
    $has_many = static::$relations['has_many'] ?? [];
    return isset($belongs_to[$name]) || isset($has_many[$name]);
  }

  /**
   * Builds a QueryBuilder for a has_many association, applying the correct
   * foreign key condition and, for polymorphic associations, the type constraint.
   *
   * @param array $options The has_many options defined in the model's $relations array.
   * @return QueryBuilder
   */
  private function setHasManyRelation($options)
  {
    $foreign_key = $options['foreign_key'];
    $class_name = $options['class_name'];
    if (isset($options['polymorphic']) && $options['polymorphic']) {
      $foreign_type = $options['foreign_type'];
      return $class_name::where([$foreign_key => $this->id, $foreign_type => static::class]);
    }
    return $class_name::where([$foreign_key => $this->id]);
  }
}
