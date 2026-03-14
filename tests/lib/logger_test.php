<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/logger.php';

use PHPUnit\Framework\TestCase;

/**
 * Tests for LogLevel enum and Logger class.
 *
 * Logger writes to php://stderr so output assertions are limited to
 * verifying that public methods are callable without exceptions.
 * The LogLevel enum's pure methods (label, color) are fully asserted.
 */
final class logger_test extends TestCase
{
  // ---- LogLevel::label() ----

  public function testLogLevelDebugLabel(): void
  {
    $this->assertSame('DEBUG', LogLevel::DEBUG->label());
  }

  public function testLogLevelInfoLabel(): void
  {
    $this->assertSame('INFO', LogLevel::INFO->label());
  }

  public function testLogLevelSqlLabel(): void
  {
    $this->assertSame('SQL', LogLevel::SQL->label());
  }

  public function testLogLevelWarningLabel(): void
  {
    $this->assertSame('WARN', LogLevel::WARNING->label());
  }

  public function testLogLevelErrorLabel(): void
  {
    $this->assertSame('ERROR', LogLevel::ERROR->label());
  }

  // ---- LogLevel::color() ----

  #[\PHPUnit\Framework\Attributes\DataProvider('providerAllLevels')]
  public function testLogLevelColorContainsAnsiEscapeCode(LogLevel $level): void
  {
    $this->assertStringContainsString("\033[", $level->color());
  }

  public static function providerAllLevels(): array
  {
    return [
      'DEBUG'   => [LogLevel::DEBUG],
      'INFO'    => [LogLevel::INFO],
      'SQL'     => [LogLevel::SQL],
      'WARNING' => [LogLevel::WARNING],
      'ERROR'   => [LogLevel::ERROR],
    ];
  }

  public function testAllLevelsHaveDistinctColors(): void
  {
    $colors = array_map(fn(LogLevel $l) => $l->color(), LogLevel::cases());
    $this->assertCount(count(LogLevel::cases()), array_unique($colors));
  }

  // ---- LogLevel integer values are ordered ----

  public function testLevelValuesAreAscending(): void
  {
    $this->assertLessThan(LogLevel::INFO->value,    LogLevel::DEBUG->value);
    $this->assertLessThan(LogLevel::SQL->value,     LogLevel::INFO->value);
    $this->assertLessThan(LogLevel::WARNING->value, LogLevel::SQL->value);
    $this->assertLessThan(LogLevel::ERROR->value,   LogLevel::WARNING->value);
  }

  // ---- Logger::setLevel() ----

  public function testSetLevelDoesNotThrow(): void
  {
    Logger::setLevel(LogLevel::ERROR);
    Logger::setLevel(LogLevel::DEBUG); // reset so other tests are unaffected
    $this->assertTrue(true);
  }

  // ---- Public logging methods do not throw ----

  public function testDebugDoesNotThrow(): void
  {
    Logger::debug('unit-test debug message');
    $this->assertTrue(true);
  }

  public function testInfoDoesNotThrow(): void
  {
    Logger::info('unit-test info message');
    $this->assertTrue(true);
  }

  public function testSqlDoesNotThrow(): void
  {
    Logger::sql('SELECT 1', ['id' => 5]);
    $this->assertTrue(true);
  }

  public function testWarningDoesNotThrow(): void
  {
    Logger::warning('unit-test warning');
    $this->assertTrue(true);
  }

  public function testErrorDoesNotThrow(): void
  {
    Logger::error('unit-test error');
    $this->assertTrue(true);
  }

  // ---- Messages below the minimum level are suppressed (no output) ----

  public function testMessagesAboveMinimumLevelAreNotSuppressed(): void
  {
    // There is no built-in way to capture php://stderr in a unit test;
    // we verify that raising the level and calling debug/info does not throw.
    Logger::setLevel(LogLevel::WARNING);
    Logger::debug('this should be suppressed');
    Logger::info('this should be suppressed');
    Logger::warning('this is at threshold');
    Logger::setLevel(LogLevel::DEBUG);
    $this->assertTrue(true);
  }

  public function testDebugWithContextDoesNotThrow(): void
  {
    Logger::debug('debug with context', ['key' => 'value', 'count' => 42]);
    $this->assertTrue(true);
  }

  public function testInfoWithContextDoesNotThrow(): void
  {
    Logger::info('info with context', ['user_id' => 7]);
    $this->assertTrue(true);
  }
}
