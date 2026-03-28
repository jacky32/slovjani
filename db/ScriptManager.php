<?php

declare(strict_types=1);

/**
 * Database script utility for opening raw connections and loading schema SQL.
 *
 * @package Database
 */
class ScriptManager
{
  /**
   * Normalizes host/port from config, supporting both host:port and separate keys.
   *
   * @param array<string, mixed> $connectionParams
   * @return array{0: string, 1: int|null}
   */
  private static function resolveHostAndPort(array $connectionParams): array
  {
    $host = (string) ($connectionParams['host'] ?? 'localhost');
    $port = null;

    if (strpos($host, ':') !== false) {
      [$parsedHost, $parsedPort] = explode(':', $host, 2);
      if ($parsedHost !== '') {
        $host = $parsedHost;
      }
      if (ctype_digit($parsedPort)) {
        $port = (int) $parsedPort;
      }
    }

    $configPort = $connectionParams['port'] ?? null;
    if ($configPort !== null && $configPort !== '' && ctype_digit((string) $configPort)) {
      $port = (int) $configPort;
    }

    // Force TCP when an explicit port is used; localhost may otherwise use a socket.
    if ($port !== null && strtolower($host) === 'localhost') {
      $host = '127.0.0.1';
    }

    return [$host, $port];
  }

  /**
   * Opens a MySQL server connection without selecting a database.
   *
   * @param array<string, mixed> $connectionParams
   * @return \mysqli
   */
  private static function connectToServer(array $connectionParams): \mysqli
  {
    [$host, $port] = self::resolveHostAndPort($connectionParams);
    try {
      $db = new \mysqli(
        $host,
        (string) ($connectionParams['user'] ?? ''),
        (string) ($connectionParams['password'] ?? ''),
        null,
        $port
      );
    } catch (\mysqli_sql_exception $exception) {
      throw new \RuntimeException('Connection failed: ' . $exception->getMessage(), 0, $exception);
    }

    if ($db->connect_errno !== 0) {
      throw new \RuntimeException('Connection failed: ' . $db->connect_error);
    }

    return $db;
  }

  /**
   * Returns expected table names parsed from db/schema.sql.
   *
   * @return list<string>
   */
  private static function expectedTablesFromSchema(): array
  {
    $schemaPath = __DIR__ . '/schema.sql';
    $sql = file_get_contents($schemaPath);
    if ($sql === false) {
      throw new \RuntimeException('Failed to read schema file at ' . $schemaPath);
    }

    preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+DB_NAME\.\`?([a-zA-Z0-9_]+)\`?/i', $sql, $matches);
    $tables = array_values(array_unique($matches[1] ?? []));
    sort($tables);

    return $tables;
  }

  /**
   * Returns current table names in a database schema.
   *
   * @param \mysqli $serverConn
   * @param string $databaseName
   * @return list<string>
   */
  private static function existingTablesInDatabase(\mysqli $serverConn, string $databaseName): array
  {
    $stmt = $serverConn->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME');
    if ($stmt === false) {
      throw new \RuntimeException('Failed to prepare information_schema query: ' . $serverConn->error);
    }

    $stmt->bind_param('s', $databaseName);
    if (!$stmt->execute()) {
      $error = $stmt->error;
      $stmt->close();
      throw new \RuntimeException('Failed to execute information_schema query: ' . $error);
    }

    $result = $stmt->get_result();
    $tables = [];
    if ($result !== false) {
      while ($row = $result->fetch_assoc()) {
        $tableName = $row['TABLE_NAME'] ?? null;
        if (is_string($tableName) && $tableName !== '') {
          $tables[] = $tableName;
        }
      }
      $result->free();
    }

    $stmt->close();

    return $tables;
  }

  /**
   * Ensures schema is initialized and checks drift against db/schema.sql.
   *
   * If the target database has no tables, it is automatically initialized by
   * executing db/schema.sql. If tables exist but differ from schema.sql, a
   * warning is emitted to stderr.
   *
   * @param array<string, mixed> $connectionParams
   * @return void
   */
  public static function ensureSchemaReady(array $connectionParams): void
  {
    $dbName = (string) ($connectionParams['dbname'] ?? '');
    if ($dbName === '') {
      throw new \InvalidArgumentException('Missing database name in connection params.');
    }

    $serverConn = self::connectToServer($connectionParams);

    try {
      $existingTables = self::existingTablesInDatabase($serverConn, $dbName);

      if (count($existingTables) === 0) {
        self::loadSchema($connectionParams);
        error_log('Schema initialized automatically because database was empty: ' . $dbName);
        return;
      }

      $expectedTables = self::expectedTablesFromSchema();
      $missingTables = array_values(array_diff($expectedTables, $existingTables));
      $unexpectedTables = array_values(array_diff($existingTables, $expectedTables));

      if ($missingTables !== [] || $unexpectedTables !== []) {
        error_log(
          'Schema drift detected for database ' . $dbName
            . ' | missing_tables=' . json_encode($missingTables)
            . ' | unexpected_tables=' . json_encode($unexpectedTables)
        );
      }
    } finally {
      $serverConn->close();
    }
  }

  /**
   * Creates a mysqli connection to the specified database.
   *
   * @param array $connectionParams Connection settings: host, user, password, dbname.
   * @return \mysqli The open database connection.
   */
  public static function connectToDatabase($connectionParams)
  {
    [$host, $port] = self::resolveHostAndPort($connectionParams);
    try {
      $db = new \mysqli(
        $host,
        (string) ($connectionParams['user'] ?? ''),
        (string) ($connectionParams['password'] ?? ''),
        (string) ($connectionParams['dbname'] ?? ''),
        $port
      );
    } catch (\mysqli_sql_exception $exception) {
      throw new \RuntimeException('Connection failed: ' . $exception->getMessage(), 0, $exception);
    }

    if ($db->connect_errno !== 0) {
      throw new \RuntimeException('Connection failed: ' . $db->connect_error);
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
    $dbName = (string) ($connectionParams['dbname'] ?? '');
    if ($dbName === '') {
      throw new \InvalidArgumentException('Missing database name in connection params.');
    }

    $conn = self::connectToServer($connectionParams);

    $schemaPath = __DIR__ . '/schema.sql';
    $sql = file_get_contents($schemaPath);
    if ($sql === false) {
      $conn->close();
      throw new \RuntimeException('Failed to read schema file at ' . $schemaPath);
    }

    $sql = str_replace('DB_NAME', $dbName, $sql);
    if ($reset) {
      $sql = 'DROP DATABASE IF EXISTS `' . $conn->real_escape_string($dbName) . '`; ' . $sql;
    }

    if (!\mysqli_multi_query($conn, $sql)) {
      $error = $conn->error;
      $conn->close();
      throw new \RuntimeException('Error setting up database schema: ' . $error);
    }

    // Consume all results to surface potential failures in subsequent statements.
    do {
      $result = $conn->store_result();
      if ($result instanceof \mysqli_result) {
        $result->free();
      }
    } while ($conn->more_results() && $conn->next_result());

    if ($conn->errno !== 0) {
      $error = $conn->error;
      $conn->close();
      throw new \RuntimeException('Schema load finished with SQL error: ' . $error);
    }

    $conn->close();
  }
}
