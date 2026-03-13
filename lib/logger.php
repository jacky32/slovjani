<?php

/**
 * Enumerates supported logger severities and related output metadata.
 */
enum LogLevel: int
{
  case DEBUG = 0;
  case INFO = 1;
  case SQL = 2;
  case WARNING = 3;
  case ERROR = 4;

  /**
   * Returns the human-readable label for this log level.
   *
   * @return string e.g. 'DEBUG', 'INFO', 'SQL', 'WARN', 'ERROR'.
   */
  public function label(): string
  {
    return match ($this) {
      self::DEBUG => 'DEBUG',
      self::INFO => 'INFO',
      self::SQL => 'SQL',
      self::WARNING => 'WARN',
      self::ERROR => 'ERROR',
    };
  }

  /**
   * Returns the ANSI escape-code colour string for this log level.
   *
   * @return string ANSI colour escape code.
   */
  public function color(): string
  {
    return match ($this) {
      self::DEBUG => "\033[90m",
      self::INFO => "\033[36m",
      self::SQL => "\033[35m",
      self::WARNING => "\033[33m",
      self::ERROR => "\033[31m",
    };
  }
}

/**
 * Structured stderr logger with ANSI colours and configurable minimum level.
 *
 * Usage:
 * Logger::debug('Debug message');
 * Logger::info('Info message');
 * Logger::sql('SELECT * FROM posts', ['id' => 5]);
 * Logger::warning('Warning message');
 * Logger::error('Error message');
 */
class Logger
{
  private static LogLevel $minLevel = LogLevel::DEBUG;
  private static string $reset = "\033[0m";
  private static string $timestampFormat = 'd/m H:i:s';

  /**
   * Sets the minimum log level; messages below this level are suppressed.
   *
   * @param LogLevel $level The minimum level to log.
   * @return void
   */
  public static function setLevel(LogLevel $level): void
  {
    self::$minLevel = $level;
  }

  /**
   * Formats and writes a log entry to stderr if the level meets the minimum threshold.
   *
   * @param LogLevel $level   The severity level of this entry.
   * @param string   $message The log message.
   * @param array    $context Optional structured context data to append.
   * @return void
   */
  private static function log(LogLevel $level, string $message, array $context = []): void
  {
    if ($level->value < self::$minLevel->value) {
      return;
    }

    $output = sprintf(
      "%s[%s] [%s]%s %s",
      $level->color(),
      date(self::$timestampFormat),
      $level->label(),
      self::$reset,
      $message
    );

    if (!empty($context)) {
      $json = json_encode($context, JSON_UNESCAPED_UNICODE);
      $output .= $level === LogLevel::SQL
        ? " {$level->color()}{$json}" . self::$reset
        : " {$json}";
    }

    file_put_contents('php://stderr', $output . PHP_EOL);
  }

  /**
   * Logs a debug-level message.
   *
   * @param string $message The message to log.
   * @param array  $context Optional structured context data.
   * @return void
   */
  public static function debug(string $message, array $context = []): void
  {
    self::log(LogLevel::DEBUG, $message, $context);
  }
  /**
   * Logs an info-level message.
   *
   * @param string $message The message to log.
   * @param array  $context Optional structured context data.
   * @return void
   */
  public static function info(string $message, array $context = []): void
  {
    self::log(LogLevel::INFO, $message, $context);
  }
  /**
   * Logs a SQL query at SQL-level severity.
   *
   * @param string $query  The SQL query string.
   * @param array  $params Bound parameter values for contextual logging.
   * @return void
   */
  public static function sql(string $query, array $params = []): void
  {
    self::log(LogLevel::SQL, $query, $params);
    // Logger::debug((new \Exception())->getTraceAsString());
  }
  /**
   * Logs a warning-level message.
   *
   * @param string $message The message to log.
   * @param array  $context Optional structured context data.
   * @return void
   */
  public static function warning(string $message, array $context = []): void
  {
    self::log(LogLevel::WARNING, $message, $context);
  }
  /**
   * Logs an error-level message.
   *
   * @param string $message The message to log.
   * @param array  $context Optional structured context data.
   * @return void
   */
  public static function error(string $message, array $context = []): void
  {
    self::log(LogLevel::ERROR, $message, $context);
  }
}
