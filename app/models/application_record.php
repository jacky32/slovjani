<?php
abstract class ApplicationRecord extends ActiveModel
{

  public function __construct($data = [])
  {
    parent::__construct($data);
    $this->setBelongsToRelations(static::$relations['belongs_to'] ?? []);
  }

  public function setBelongsToRelations($belongs_to = [])
  {
    foreach ($belongs_to as $relation => $options) {
      $foreign_key = $this->{$options['foreign_key']};
      $class_name = $options['class_name'];

      $this->{$relation} = $class_name::find($foreign_key);
    }
  }
}
