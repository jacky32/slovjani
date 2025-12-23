<?php
class ApplicationRecord extends ActiveModel
{
  protected $db;
  protected $connection;

  public function __construct($data = [], $db_attributes = [])
  {
    $this->db = new Database();
    $this->connection = $this->db->getConnection();

    foreach ($db_attributes as $attribute) {
      if (isset($data[$attribute])) {
        $this->$attribute = $data[$attribute];
      }
    }
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
}
