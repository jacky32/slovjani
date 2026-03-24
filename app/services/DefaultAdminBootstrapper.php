<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * Ensures a first administrator account exists on fresh installations.
 *
 * @package Services
 */
class DefaultAdminBootstrapper
{

  public static function canBootstrap(): bool
  {
    return !self::usersTableHasAnyUser();
  }

  public static function ensureExists(\Delight\Auth\Auth $auth): void
  {
    if (!self::canBootstrap()) {
      return;
    }

    try {
      $newUserId = $auth->admin()->createUser(
        getenv('DEFAULT_ADMIN_EMAIL') ?: 'admin@admin.cz',
        getenv('DEFAULT_ADMIN_PASSWORD') ?: 'adminadmin123',
        getenv('DEFAULT_ADMIN_USERNAME') ?: 'admin'
      );
      $auth->admin()->addRoleForUserById($newUserId, \Delight\Auth\Role::ADMIN);
      \Logger::info('Bootstrapped default admin account.');
    } catch (\Throwable $exception) {
      \Logger::error('Default admin bootstrap failed: ' . $exception->getMessage());
      throw $exception;
    }
  }

  private static function usersTableHasAnyUser(): bool
  {
    $count = User::exists();
    return $count > 0;
  }
}

class_alias(__NAMESPACE__ . '\\DefaultAdminBootstrapper', 'DefaultAdminBootstrapper');
