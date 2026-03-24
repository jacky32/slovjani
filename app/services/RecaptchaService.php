<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Verifies Google reCAPTCHA tokens for login forms.
 *
 * Verification strategy:
 * 1) Try reCAPTCHA v3 token first
 * 2) If v3 verification is missing or fails, try reCAPTCHA v2 token
 *
 * @package Services
 */
class RecaptchaService
{
  private const VERIFY_ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

  /** @var array<string, mixed> */
  private array $config;

  /**
   * @var callable
   * @phpstan-var callable(string, string, ?string): array<string, mixed>
   */
  private $httpClient;

  public function __construct(?array $config = null, ?callable $httpClient = null)
  {
    if ($config === null) {
      /** @var array{recaptcha?: array<string, mixed>} $appConfig */
      $appConfig = require __DIR__ . '/../../config/Application.php';
      $config = $appConfig['recaptcha'] ?? [];
    }

    $this->config = $config;
    $this->httpClient = $httpClient ?? [$this, 'sendVerificationRequest'];
  }

  public function isEnabled(): bool
  {
    return (bool) ($this->config['enabled'] ?? false);
  }

  public function getV3SiteKey(): string
  {
    return trim((string) ($this->config['v3_site_key'] ?? ''));
  }

  public function getV2SiteKey(): string
  {
    return trim((string) ($this->config['v2_site_key'] ?? ''));
  }

  /**
   * @param array<string, mixed> $requestData
   * @return array{success: bool, requires_v2: bool, reason_key: string}
   */
  public function verifyLogin(array $requestData, ?string $remoteIp = null): array
  {
    if (!$this->isEnabled()) {
      return [
        'success' => true,
        'requires_v2' => false,
        'reason_key' => '',
      ];
    }

    $v3IsValid = $this->verifyV3($requestData, $remoteIp);
    if ($v3IsValid) {
      return [
        'success' => true,
        'requires_v2' => false,
        'reason_key' => '',
      ];
    }

    $v2Token = trim((string) ($requestData['g-recaptcha-response'] ?? ''));
    if ($v2Token === '') {
      return [
        'success' => false,
        'requires_v2' => true,
        'reason_key' => 'sessions.recaptcha.v2_required',
      ];
    }

    $v2Secret = trim((string) ($this->config['v2_secret_key'] ?? ''));
    if ($v2Secret === '') {
      \Logger::warning('reCAPTCHA v2 secret key is missing.');
      return [
        'success' => false,
        'requires_v2' => false,
        'reason_key' => 'sessions.recaptcha.verification_failed',
      ];
    }

    try {
      $v2Response = $this->verifyToken(secret: $v2Secret, token: $v2Token, remoteIp: $remoteIp);
    } catch (\RuntimeException $exception) {
      \Logger::warning('reCAPTCHA v2 verification failed: ' . $exception->getMessage());
      return [
        'success' => false,
        'requires_v2' => false,
        'reason_key' => 'sessions.recaptcha.verification_failed',
      ];
    }

    if ((bool) ($v2Response['success'] ?? false)) {
      return [
        'success' => true,
        'requires_v2' => false,
        'reason_key' => '',
      ];
    }

    return [
      'success' => false,
      'requires_v2' => false,
      'reason_key' => 'sessions.recaptcha.verification_failed',
    ];
  }

  /**
   * @param array<string, mixed> $requestData
   */
  private function verifyV3(array $requestData, ?string $remoteIp): bool
  {
    $v3Token = trim((string) ($requestData['recaptcha_v3_token'] ?? ''));
    $v3Secret = trim((string) ($this->config['v3_secret_key'] ?? ''));
    $expectedAction = trim((string) ($this->config['v3_action'] ?? 'login'));
    $scoreThreshold = (float) ($this->config['v3_score_threshold'] ?? 0.5);

    if ($v3Token === '' || $v3Secret === '') {
      return false;
    }

    try {
      $v3Response = $this->verifyToken(secret: $v3Secret, token: $v3Token, remoteIp: $remoteIp);
    } catch (\RuntimeException $exception) {
      \Logger::warning('reCAPTCHA v3 verification failed: ' . $exception->getMessage());
      return false;
    }

    $isSuccess = (bool) ($v3Response['success'] ?? false);
    $score = (float) ($v3Response['score'] ?? 0.0);
    $action = (string) ($v3Response['action'] ?? '');

    if (!$isSuccess) {
      return false;
    }

    if ($action !== '' && $expectedAction !== '' && $action !== $expectedAction) {
      return false;
    }

    return $score >= $scoreThreshold;
  }

  /**
   * @return array<string, mixed>
   */
  private function verifyToken(string $secret, string $token, ?string $remoteIp): array
  {
    $httpClient = $this->httpClient;
    return $httpClient($secret, $token, $remoteIp);
  }

  /**
   * @return array<string, mixed>
   */
  private function sendVerificationRequest(string $secret, string $token, ?string $remoteIp): array
  {
    $payload = [
      'secret' => $secret,
      'response' => $token,
    ];

    if ($remoteIp !== null && trim($remoteIp) !== '') {
      $payload['remoteip'] = trim($remoteIp);
    }

    $options = [
      'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($payload, '', '&', PHP_QUERY_RFC3986),
        'timeout' => 8,
      ],
    ];

    $context = stream_context_create($options);
    $responseBody = @file_get_contents(self::VERIFY_ENDPOINT, false, $context);

    if ($responseBody === false) {
      throw new \RuntimeException('Unable to contact reCAPTCHA verification endpoint.');
    }

    try {
      $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $exception) {
      throw new \RuntimeException('Invalid response from reCAPTCHA verification endpoint.', 0, $exception);
    }

    return is_array($decoded) ? $decoded : [];
  }
}

