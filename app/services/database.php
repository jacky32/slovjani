<?php
class Database
{
  private $connectionParams;

  /**
   * @var mysqli
   */
  private $db;


  public function __construct()
  {
    $appConfig = require 'config/application.php';
    $this->connectionParams = $appConfig['connection'];

    $this->db = $this->connect();

    if (mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
      if (strpos(mysqli_connect_error(), "Unknown database") !== NULL) {
        ScriptManager::loadSchema($this->connectionParams);
      }
      $this->db = $this->connect();
    }

    if (mysqli_connect_errno()) {
      throw new Exception(sprintf("Connect failed: %s\n", mysqli_connect_error()));
    }
  }


  private function connect()
  {
    $this->db = ScriptManager::connectToDatabase($this->connectionParams);
    return $this->db;
  }

  public function getConnection()
  {
    return $this->db;
  }
}
