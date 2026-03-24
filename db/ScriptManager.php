<?php

/**
 * Database script utility for opening raw connections and loading schema SQL.
 *
 * @package Database
 */
class ScriptManager
{
  /**
   * Creates a mysqli connection to the specified database.
   *
   * @param array $connectionParams Connection settings: host, user, password, dbname.
   * @return \mysqli The open database connection.
   */
  public static function connectToDatabase($connectionParams)
  {
    $db = new mysqli(
      $connectionParams['host'],
      $connectionParams['user'],
      $connectionParams['password'],
      $connectionParams['dbname']
    );

    if ($db->connect_error) {
      die("Connection failed: " . $db->connect_error);
    }

    return $db;
  }
  /**
   * Executes the schema SQL file against the server, optionally dropping and
   * recreating the database first.
   *
   * @param array $connectionParams Connection settings: host, user, password, dbname.
   * @param bool  $reset            When true, drops the existing database before loading.
   * @return void
   * @throws \Exception If the multi-query execution fails.
   */
  public static function loadSchema($connectionParams, $reset = false)
  {
    $output = [];
    // Connect to db without specifying a database
    $conn = new mysqli($connectionParams['host'], $connectionParams['user'], $connectionParams['password']);

    $conn->store_result();

    $sql = file_get_contents('db/schema.sql');
    $sql = str_replace("DB_NAME", $connectionParams['dbname'], $sql);
    if ($reset) {
      $output[] = "<br />Dropping existing database " . $connectionParams['dbname'];
      // echo "<br />Dropping existing database " . $connectionParams['dbname'];
      $sql = "DROP DATABASE IF EXISTS " . $connectionParams['dbname'] . "; " . $sql;
    }
    if (mysqli_multi_query($conn, $sql)) {
      $output[] = "<br />SQL load schema script executed successfully";
    } else {
      throw new \Exception("Error of database setting up: " . $conn->error);
    }
    $conn->close();
  }
}
