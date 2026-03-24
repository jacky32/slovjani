<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Database connection bootstrapper with first-run schema initialisation.
 *
 * @package Services
 */
class Database
{
  private $connectionParams;

  /**
   * @var \mysqli
   */
  private $db;


  /**
   * Loads application config, opens a mysqli connection, and attempts to
   * bootstrap the schema automatically on first run if the database is missing.
   *
   * @throws \Exception If a connection cannot be established after schema load.
   */
  public function __construct()
  {
    $appConfig = require 'config/Application.php';
    $this->connectionParams = $appConfig['connection'];

    $this->db = $this->connect();

    if (mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
      if (strpos(mysqli_connect_error(), "Unknown database") !== NULL) {
        \ScriptManager::loadSchema($this->connectionParams);
      }
      $this->db = $this->connect();
    }

    if (mysqli_connect_errno()) {
      throw new \Exception(sprintf("Connect failed: %s\n", mysqli_connect_error()));
    }
  }


  /**
   * Opens (or re-opens) the mysqli connection using the stored connection params.
   *
   * @return \mysqli The active database connection.
   */
  private function connect()
  {
    $this->db = \ScriptManager::connectToDatabase($this->connectionParams);
    return $this->db;
  }

  /**
   * Returns the active mysqli connection.
   *
   * @return \mysqli
   */
  public function getConnection()
  {
    return $this->db;
  }
}

