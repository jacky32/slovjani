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
