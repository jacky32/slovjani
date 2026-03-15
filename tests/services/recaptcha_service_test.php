<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/services/recaptcha_service.php';

use PHPUnit\Framework\TestCase;

final class recaptcha_service_test extends TestCase
{
  public function testReturnsSuccessWhenRecaptchaIsDisabled(): void
  {
    $service = new RecaptchaService([
      'enabled' => false,
    ]);

    $result = $service->verifyLogin([]);

    $this->assertTrue($result['success']);
    $this->assertFalse($result['requires_v2']);
  }

  public function testAcceptsValidV3TokenWithoutV2Fallback(): void
  {
    $service = new RecaptchaService(
      [
        'enabled' => true,
        'v3_secret_key' => 'secret-v3',
        'v3_action' => 'login',
        'v3_score_threshold' => 0.5,
      ],
      static function (string $secret, string $token, ?string $remoteIp = null): array {
        if ($secret === 'secret-v3' && $token === 'v3-token') {
          return [
            'success' => true,
            'action' => 'login',
            'score' => 0.9,
          ];
        }

        return ['success' => false];
      }
    );

    $result = $service->verifyLogin([
      'recaptcha_v3_token' => 'v3-token',
    ]);

    $this->assertTrue($result['success']);
    $this->assertFalse($result['requires_v2']);
  }

  public function testRequiresV2WhenV3FailsAndNoV2TokenIsProvided(): void
  {
    $service = new RecaptchaService(
      [
        'enabled' => true,
        'v3_secret_key' => 'secret-v3',
        'v3_action' => 'login',
        'v3_score_threshold' => 0.8,
      ],
      static function (string $secret, string $token, ?string $remoteIp = null): array {
        return [
          'success' => true,
          'action' => 'login',
          'score' => 0.2,
        ];
      }
    );

    $result = $service->verifyLogin([
      'recaptcha_v3_token' => 'v3-token',
    ]);

    $this->assertFalse($result['success']);
    $this->assertTrue($result['requires_v2']);
    $this->assertSame('sessions.recaptcha.v2_required', $result['reason_key']);
  }

  public function testFallsBackToV2WhenV3Fails(): void
  {
    $service = new RecaptchaService(
      [
        'enabled' => true,
        'v3_secret_key' => 'secret-v3',
        'v3_action' => 'login',
        'v3_score_threshold' => 0.8,
        'v2_secret_key' => 'secret-v2',
      ],
      static function (string $secret, string $token, ?string $remoteIp = null): array {
        if ($secret === 'secret-v3') {
          return [
            'success' => true,
            'action' => 'login',
            'score' => 0.2,
          ];
        }

        if ($secret === 'secret-v2') {
          return ['success' => true];
        }

        return ['success' => false];
      }
    );

    $result = $service->verifyLogin([
      'recaptcha_v3_token' => 'v3-token',
      'g-recaptcha-response' => 'v2-token',
    ]);

    $this->assertTrue($result['success']);
    $this->assertFalse($result['requires_v2']);
  }

  public function testReturnsFailureWhenV2AlsoFails(): void
  {
    $service = new RecaptchaService(
      [
        'enabled' => true,
        'v3_secret_key' => 'secret-v3',
        'v3_action' => 'login',
        'v3_score_threshold' => 0.8,
        'v2_secret_key' => 'secret-v2',
      ],
      static function (string $secret, string $token, ?string $remoteIp = null): array {
        return ['success' => false];
      }
    );

    $result = $service->verifyLogin([
      'recaptcha_v3_token' => 'v3-token',
      'g-recaptcha-response' => 'v2-token',
    ]);

    $this->assertFalse($result['success']);
    $this->assertFalse($result['requires_v2']);
    $this->assertSame('sessions.recaptcha.verification_failed', $result['reason_key']);
  }
}
