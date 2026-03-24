<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/services/GoogleCalendarService.php';

use PHPUnit\Framework\TestCase;

final class GoogleCalendarServiceTest extends TestCase
{
  /** @var array<string, string|null> */
  private array $originalEnv = [];

  protected function setUp(): void
  {
    $this->setEnv('GOOGLE_PUBLIC_CALENDAR_ID', 'public-calendar@example.com');
    $this->setEnv('GOOGLE_PRIVATE_CALENDAR_ID', 'private-calendar@example.com');
    $this->setEnv('GOOGLE_SERVICE_ACCOUNT_EMAIL', 'service-account@example.iam.gserviceaccount.com');
    $this->setEnv('GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY', 'dummy-private-key');
    $this->setEnv('GOOGLE_CALENDAR_TIMEZONE', 'UTC');
  }

  protected function tearDown(): void
  {
    foreach ($this->originalEnv as $key => $value) {
      if ($value === null) {
        putenv($key);
      } else {
        putenv($key . '=' . $value);
      }
    }

    $this->originalEnv = [];
  }

  public function testConstructorUsesPrivateCalendarByDefault(): void
  {
    $service = new GoogleCalendarService();

    $this->assertSame('private-calendar@example.com', $this->readPrivateProperty($service, 'calendarId'));
  }

  public function testConstructorUsesPublicCalendarWhenRequested(): void
  {
    $service = new GoogleCalendarService(true);

    $this->assertSame('public-calendar@example.com', $this->readPrivateProperty($service, 'calendarId'));
  }

  public function testInsertTimedEventThrowsForInvalidDatetimeBeforeAnyHttpCall(): void
  {
    $service = new GoogleCalendarService();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid event datetime provided');

    $service->insertTimedEvent('Meeting', 'not-a-date', '2026-03-15 20:00:00');
  }

  public function testDestroyEventThrowsForEmptyEventId(): void
  {
    $service = new GoogleCalendarService();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Google Calendar event ID cannot be empty.');

    $service->destroyEvent('  ');
  }

  public function testThrowsWhenRequiredConfigurationIsMissing(): void
  {
    $this->setEnv('GOOGLE_PRIVATE_CALENDAR_ID', '');

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Google Calendar configuration is incomplete. Missing: calendar_id');

    new GoogleCalendarService();
  }

  private function setEnv(string $key, ?string $value): void
  {
    if (!array_key_exists($key, $this->originalEnv)) {
      $existing = getenv($key);
      $this->originalEnv[$key] = $existing === false ? null : $existing;
    }

    if ($value === null) {
      putenv($key);
      return;
    }

    putenv($key . '=' . $value);
  }

  private function readPrivateProperty(object $object, string $property): mixed
  {
    $reflection = new ReflectionObject($object);
    $reflectionProperty = $reflection->getProperty($property);
    $reflectionProperty->setAccessible(true);
    return $reflectionProperty->getValue($object);
  }
}
