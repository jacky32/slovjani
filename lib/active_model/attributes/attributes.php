<?php

trait Attributes
{
  protected array $_attributes = [];
  protected static array $db_attributes = [];

  /**
   * Initialize attributes from data array based on db_attributes
   */
  protected function initializeAttributes(array $data = [])
  {
    foreach (static::$db_attributes as $attribute) {
      if (isset($data[$attribute])) {
        $this->_attributes[$attribute] = $data[$attribute];
      }
    }
  }

  /**
   * Get an attribute value
   */
  protected function getAttribute($name)
  {
    if (in_array($name, static::$db_attributes ?? [])) {
      return $this->_attributes[$name] ?? null;
    }
    return null;
  }

  /**
   * Set an attribute value
   */
  protected function setAttribute($name, $value)
  {
    $this->_attributes[$name] = $value;
  }

  /**
   * Check if an attribute exists
   */
  protected function hasAttribute($name)
  {
    return isset($this->_attributes[$name]);
  }
}
