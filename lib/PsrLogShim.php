<?php

declare(strict_types=1);

namespace Psr\Log;

if (!interface_exists(LoggerInterface::class)) {
  interface LoggerInterface
  {
    public function emergency(string|\Stringable $message, array $context = []): void;

    public function alert(string|\Stringable $message, array $context = []): void;

    public function critical(string|\Stringable $message, array $context = []): void;

    public function error(string|\Stringable $message, array $context = []): void;

    public function warning(string|\Stringable $message, array $context = []): void;

    public function notice(string|\Stringable $message, array $context = []): void;

    public function info(string|\Stringable $message, array $context = []): void;

    public function debug(string|\Stringable $message, array $context = []): void;

    public function log($level, string|\Stringable $message, array $context = []): void;
  }
}

if (!class_exists(LogLevel::class)) {
  final class LogLevel
  {
    public const EMERGENCY = 'emergency';
    public const ALERT = 'alert';
    public const CRITICAL = 'critical';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const NOTICE = 'notice';
    public const INFO = 'info';
    public const DEBUG = 'debug';
  }
}
