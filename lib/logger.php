<?php

enum LogLevel: int
{
  case DEBUG = 0;
  case INFO = 1;
  case SQL = 2;
  case WARNING = 3;
  case ERROR = 4;

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
 * Simple Logger with log levels
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

  public static function setLevel(LogLevel $level): void
  {
    self::$minLevel = $level;
  }

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

  public static function debug(string $message, array $context = []): void
  {
    self::log(LogLevel::DEBUG, $message, $context);
  }
  public static function info(string $message, array $context = []): void
  {
    self::log(LogLevel::INFO, $message, $context);
  }
  public static function sql(string $query, array $params = []): void
  {
    self::log(LogLevel::SQL, $query, $params);
  }
  public static function warning(string $message, array $context = []): void
  {
    self::log(LogLevel::WARNING, $message, $context);
  }
  public static function error(string $message, array $context = []): void
  {
    self::log(LogLevel::ERROR, $message, $context);
  }
}
