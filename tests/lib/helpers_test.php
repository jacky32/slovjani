<?php

declare(strict_types=1);

require __DIR__ . '/../../lib/helpers.php';

use PHPUnit\Framework\TestCase;

final class helpers_test extends TestCase
{
  public function testToSnakeCase(): void
  {
    $this->assertSame('user_profile', toSnakeCase('UserProfile'));
    $this->assertSame('user_profile_test', toSnakeCase('UserProfileTest'));
    $this->assertSame('user', toSnakeCase('User'));
  }

  public function testToPascalCase(): void
  {
    $this->assertSame('UserProfile', toPascalCase('user_profile'));
    $this->assertSame('UserProfileTest', toPascalCase('user_profile_test'));
    $this->assertSame('User', toPascalCase('user'));
  }
}
