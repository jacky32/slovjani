<?php

declare(strict_types=1);

namespace App\Services;

use Closure;

/**
 * Minimal Google Calendar API client for inserting and deleting events.
 *
 * The service authenticates using a Google service account and exchanges a
 * signed JWT for an OAuth access token before calling Calendar API endpoints.
 *
 * Supported configuration keys:
 * - calendar_id
 * - service_account_email
 * - private_key
 * - delegated_user
 * - token_uri
 * - base_uri
 * - timeout_seconds
 * - time_zone
 *
 * @package Services
 */
class GoogleCalendarService
{
  private const CALENDAR_SCOPE = 'https://www.googleapis.com/auth/calendar';

  private readonly string $calendarId;
  private readonly string $serviceAccountEmail;
  private readonly string $privateKey;
  private readonly ?string $delegatedUser;
  private readonly string $tokenUri;
  private readonly string $baseUri;
  private readonly int $timeoutSeconds;
  private readonly string $defaultTimeZone;
  private readonly Closure $httpClient;
  private readonly Closure $clock;

  /**
   * @param bool $isPubliclyVisible Sends the event to the public calendar if true, otherwise to the private calendar.
   */
  public function __construct(bool $isPubliclyVisible = false)
  {
    $resolvedConfig = $this->loadDefaultConfig();

    $this->calendarId = trim((string) ($isPubliclyVisible ? $resolvedConfig['public_calendar_id'] ?? '' : $resolvedConfig['private_calendar_id'] ?? ''));
    $this->serviceAccountEmail = trim((string) ($resolvedConfig['service_account_email'] ?? ''));
    $this->privateKey = $this->normalizePrivateKey((string) ($resolvedConfig['private_key'] ?? ''));
    $this->delegatedUser = $this->normalizeOptionalString($resolvedConfig['delegated_user'] ?? null);
    $this->tokenUri = rtrim(trim((string) ($resolvedConfig['token_uri'] ?? 'https://oauth2.googleapis.com/token')), '/');
    $this->baseUri = rtrim(trim((string) ($resolvedConfig['base_uri'] ?? 'https://www.googleapis.com/calendar/v3')), '/');
    $this->timeoutSeconds = max(1, (int) ($resolvedConfig['timeout_seconds'] ?? 15));
    $this->defaultTimeZone = trim((string) ($resolvedConfig['time_zone'] ?? date_default_timezone_get()));
    $this->httpClient = Closure::fromCallable([$this, 'sendCurlRequest']);
    $this->clock = Closure::fromCallable(static fn(): int => time());

    $this->assertConfiguration();
  }

  /**
   * Inserts a timed Google Calendar event using a simplified input signature.
   *
   * @return array<string, mixed>
   */
  public function insertTimedEvent(
    string $summary,
    string $startDateTime,
    string $endDateTime,
    ?string $description = null,
    ?string $timeZone = null
  ): array {
    $resolvedTimeZone = $timeZone ?: $this->defaultTimeZone;
    $event = $this->insertEvent([
      'summary' => $summary,
      'description' => $description,
      'start' => [
        'dateTime' => $this->formatDateTime($startDateTime, $resolvedTimeZone),
        'timeZone' => $resolvedTimeZone,
      ],
      'end' => [
        'dateTime' => $this->formatDateTime($endDateTime, $resolvedTimeZone),
        'timeZone' => $resolvedTimeZone,
      ],
    ]);
    return $event;
  }

  /**
   * Lists Google Calendar events with optional query parameters.
   */
  public function listEvents(array $queryParams = []): array
  {
    $response = $this->request(
      method: 'GET',
      url: $this->eventsEndpoint() . '?' . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986)
    );
    \Logger::info("Listed Google Calendar events with query: " . json_encode($queryParams));
    return $this->decodeJsonBody($response['body'] ?? '', 'Google Calendar list events response was not valid JSON.');
  }

  /**
   * Inserts a Google Calendar event using the raw Calendar API payload.
   *
   * @param array<string, mixed> $eventData
   * @return array<string, mixed>
   */
  public function insertEvent(array $eventData): array
  {
    $response = $this->request(
      method: 'POST',
      url: $this->eventsEndpoint(),
      payload: $eventData
    );

    return $this->decodeJsonBody($response['body'] ?? '', 'Google Calendar insert response was not valid JSON.');
  }

  /**
   * Deletes a Google Calendar event by its Google-assigned event ID.
   */
  public function destroyEvent(string $eventId): void
  {
    $normalizedEventId = trim($eventId);
    if ($normalizedEventId === '') {
      throw new \InvalidArgumentException('Google Calendar event ID cannot be empty.');
    }

    $this->request(
      method: 'DELETE',
      url: $this->eventsEndpoint() . '/' . rawurlencode($normalizedEventId)
    );
  }

  /**
   * @return array<string, mixed>
   */
  private function request(string $method, string $url, ?array $payload = null): array
  {
    $headers = [
      'Accept: application/json',
      'Authorization: Bearer ' . $this->fetchAccessToken(),
    ];

    $body = null;
    if ($payload !== null) {
      $headers[] = 'Content-Type: application/json';
      $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    $response = ($this->httpClient)($method, $url, $headers, $body, $this->timeoutSeconds);
    $status = (int) ($response['status'] ?? 0);
    $responseBody = (string) ($response['body'] ?? '');

    if ($status < 200 || $status >= 300) {
      throw new \RuntimeException(sprintf(
        'Google Calendar API request failed with status %d: %s',
        $status,
        $this->compactBody($responseBody)
      ));
    }

    return $response;
  }

  private function fetchAccessToken(): string
  {
    $response = ($this->httpClient)(
      'POST',
      $this->tokenUri,
      [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
      ],
      http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $this->buildSignedJwt(),
      ], '', '&', PHP_QUERY_RFC3986),
      $this->timeoutSeconds
    );

    $status = (int) ($response['status'] ?? 0);
    $responseBody = (string) ($response['body'] ?? '');

    if ($status < 200 || $status >= 300) {
      throw new \RuntimeException(sprintf(
        'Google OAuth token request failed with status %d: %s',
        $status,
        $this->compactBody($responseBody)
      ));
    }

    $payload = $this->decodeJsonBody($responseBody, 'Google OAuth token response was not valid JSON.');
    $accessToken = $payload['access_token'] ?? null;

    if (!is_string($accessToken) || trim($accessToken) === '') {
      throw new \RuntimeException('Google OAuth token response did not contain an access token.');
    }

    return $accessToken;
  }

  private function buildSignedJwt(): string
  {
    $issuedAt = (int) ($this->clock)();
    $claims = [
      'iss' => $this->serviceAccountEmail,
      'scope' => self::CALENDAR_SCOPE,
      'aud' => $this->tokenUri,
      'iat' => $issuedAt,
      'exp' => $issuedAt + 3600,
    ];

    if ($this->delegatedUser !== null) {
      $claims['sub'] = $this->delegatedUser;
    }

    $encodedHeader = $this->base64UrlEncode(json_encode([
      'alg' => 'RS256',
      'typ' => 'JWT',
    ], JSON_THROW_ON_ERROR));
    $encodedClaims = $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    $unsignedToken = $encodedHeader . '.' . $encodedClaims;

    $privateKey = openssl_pkey_get_private($this->privateKey);
    if ($privateKey === false) {
      throw new \RuntimeException('Google service account private key is invalid.');
    }

    $signature = '';
    $signResult = openssl_sign($unsignedToken, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    if ($signResult !== true) {
      throw new \RuntimeException('Failed to sign the Google OAuth JWT assertion.');
    }

    return $unsignedToken . '.' . $this->base64UrlEncode($signature);
  }

  /**
   * @return array<string, mixed>
   */
  private function loadDefaultConfig(): array
  {
    /** @var array{google_calendar?: array<string, mixed>} $appConfig */
    $appConfig = require __DIR__ . '/../../config/Application.php';

    return $appConfig['google_calendar'] ?? [];
  }

  private function assertConfiguration(): void
  {
    $missing = [];

    if ($this->calendarId === '') {
      $missing[] = 'calendar_id';
    }
    if ($this->serviceAccountEmail === '') {
      $missing[] = 'service_account_email';
    }
    if ($this->privateKey === '') {
      $missing[] = 'private_key';
    }

    if ($missing !== []) {
      throw new \InvalidArgumentException(
        'Google Calendar configuration is incomplete. Missing: ' . implode(', ', $missing)
      );
    }
  }

  private function eventsEndpoint(): string
  {
    return $this->baseUri . '/calendars/' . rawurlencode($this->calendarId) . '/events';
  }

  private function formatDateTime(string $dateTime, string $timeZone): string
  {
    try {
      return (new \DateTimeImmutable($dateTime, new \DateTimeZone($timeZone)))->format(\DateTimeInterface::RFC3339);
    } catch (\Exception $exception) {
      throw new \InvalidArgumentException('Invalid event datetime provided: ' . $dateTime, 0, $exception);
    }
  }

  private function normalizePrivateKey(string $privateKey): string
  {
    return trim(str_replace('\\n', PHP_EOL, $privateKey));
  }

  private function normalizeOptionalString(mixed $value): ?string
  {
    if (!is_string($value)) {
      return null;
    }

    $trimmed = trim($value);
    return $trimmed === '' ? null : $trimmed;
  }

  /**
   * @return array<string, mixed>
   */
  private function decodeJsonBody(string $body, string $errorMessage): array
  {
    if (trim($body) === '') {
      return [];
    }

    try {
      $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $exception) {
      throw new \RuntimeException($errorMessage, 0, $exception);
    }

    return is_array($decoded) ? $decoded : [];
  }

  private function base64UrlEncode(string $value): string
  {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
  }

  private function compactBody(string $body): string
  {
    $normalized = preg_replace('/\s+/', ' ', trim($body)) ?: '';
    return substr($normalized, 0, 300);
  }

  /**
   * @param array<int, string> $headers
   * @return array{status:int, headers:array<string, string>, body:string}
   */
  private function sendCurlRequest(string $method, string $url, array $headers, ?string $body, int $timeoutSeconds): array
  {
    if (!function_exists('curl_init')) {
      throw new \RuntimeException('The cURL extension is required for Google Calendar API requests.');
    }

    $handle = curl_init();
    if ($handle === false) {
      throw new \RuntimeException('Unable to initialise cURL for Google Calendar API requests.');
    }

    $options = [
      CURLOPT_URL => $url,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_TIMEOUT => $timeoutSeconds,
    ];

    if ($body !== null) {
      $options[CURLOPT_POSTFIELDS] = $body;
    }

    curl_setopt_array($handle, $options);
    $rawResponse = curl_exec($handle);

    if ($rawResponse === false) {
      $error = curl_error($handle);
      throw new \RuntimeException('Google Calendar API request failed: ' . $error);
    }

    $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $rawHeaders = substr($rawResponse, 0, $headerSize) ?: '';
    $responseBody = substr($rawResponse, $headerSize) ?: '';

    return [
      'status' => $status,
      'headers' => $this->parseHeaders($rawHeaders),
      'body' => $responseBody,
    ];
  }

  /**
   * @return array<string, string>
   */
  private function parseHeaders(string $rawHeaders): array
  {
    $parsedHeaders = [];

    foreach (preg_split('/\r\n|\r|\n/', trim($rawHeaders)) ?: [] as $line) {
      if (!str_contains($line, ':')) {
        continue;
      }

      [$name, $value] = explode(':', $line, 2);
      $parsedHeaders[trim($name)] = trim($value);
    }

    return $parsedHeaders;
  }
}

