<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/services/GuestAuth.php';

use App\Services\GuestAuth;
use PHPUnit\Framework\TestCase;

/**
 * Tests for GuestAuth.
 *
 * GuestAuth is a thin stub that always represents an unauthenticated guest,
 * so all three methods have simple, deterministic return values.
 */
final class GuestAuthTest extends TestCase
{
  private GuestAuth $auth;

  protected function setUp(): void
  {
    $this->auth = new GuestAuth();
  }

  // ---- isLoggedIn() ----

  public function testIsLoggedInReturnsFalse(): void
  {
    $this->assertFalse($this->auth->isLoggedIn());
  }

  public function testIsLoggedInAlwaysReturnsFalseOnNewInstance(): void
  {
    $another = new GuestAuth();
    $this->assertFalse($another->isLoggedIn());
  }

  // ---- getUserId() ----

  public function testGetUserIdReturnsNull(): void
  {
    $this->assertNull($this->auth->getUserId());
  }

  public function testGetUserIdIsNullNotAnInteger(): void
  {
    $result = $this->auth->getUserId();
    $this->assertIsNotInt($result);
  }

  // ---- hasRole() ----

  public function testHasRoleReturnsFalseForRoleZero(): void
  {
    $this->assertFalse($this->auth->hasRole(0));
  }

  public function testHasRoleReturnsFalseForRoleOne(): void
  {
    $this->assertFalse($this->auth->hasRole(1));
  }

  public function testHasRoleReturnsFalseForArbitraryRoles(): void
  {
    $this->assertFalse($this->auth->hasRole(100));
    $this->assertFalse($this->auth->hasRole(999));
    $this->assertFalse($this->auth->hasRole(-1));
  }

  // ---- return types ----

  public function testIsLoggedInReturnsBool(): void
  {
    $this->assertIsBool($this->auth->isLoggedIn());
  }

  public function testHasRoleReturnsBool(): void
  {
    $this->assertIsBool($this->auth->hasRole(1));
  }
}
