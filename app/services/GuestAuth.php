<?php

declare(strict_types=1);

namespace App\Services;

/**
 * A minimal auth stub that represents an unauthenticated (guest) user.
 * Used by StaticPageGenerator so that rendered HTML never contains
 * session-specific or user-specific content.
 *
 * @package Services
 */
class GuestAuth
{
  /**
   * Always returns false – the guest is never logged in.
   */
  public function isLoggedIn(): bool
  {
    return false;
  }

  /**
   * Always returns null – no user ID for a guest.
   */
  public function getUserId(): ?int
  {
    return null;
  }

  /**
   * Always returns false – a guest has no roles.
   */
  public function hasRole(int $role): bool
  {
    return false;
  }
}

