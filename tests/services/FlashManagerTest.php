<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/services/FlashManager.php';

use App\Services\FlashManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FlashManager.
 *
 * FlashManager reads from and writes to $_SESSION.
 * Each test starts with a clean session state via setUp()/tearDown().
 */
final class FlashManagerTest extends TestCase
{
  protected function setUp(): void
  {
    $_SESSION = [];
  }

  protected function tearDown(): void
  {
    $_SESSION = [];
  }

  // ---- addFlash() ----

  public function testAddFlashSuccessStoresMessage(): void
  {
    FlashManager::addFlash('success', 'Record created.');
    $this->assertArrayHasKey('success', $_SESSION['FLASHES']);
    $this->assertContains('Record created.', $_SESSION['FLASHES']['success']);
  }

  public function testAddFlashErrorStoresMessage(): void
  {
    FlashManager::addFlash('error', 'Something went wrong.');
    $this->assertContains('Something went wrong.', $_SESSION['FLASHES']['error']);
  }

  public function testAddFlashInfoStoresMessage(): void
  {
    FlashManager::addFlash('info', 'Note this.');
    $this->assertContains('Note this.', $_SESSION['FLASHES']['info']);
  }

  public function testAddFlashWarningStoresMessage(): void
  {
    FlashManager::addFlash('warning', 'Be careful.');
    $this->assertContains('Be careful.', $_SESSION['FLASHES']['warning']);
  }

  public function testAddFlashMultipleMessagesForSameType(): void
  {
    FlashManager::addFlash('success', 'First.');
    FlashManager::addFlash('success', 'Second.');
    $this->assertCount(2, $_SESSION['FLASHES']['success']);
    $this->assertSame('First.',  $_SESSION['FLASHES']['success'][0]);
    $this->assertSame('Second.', $_SESSION['FLASHES']['success'][1]);
  }

  public function testAddFlashMultipleTypesAreStoredSeparately(): void
  {
    FlashManager::addFlash('success', 'Done.');
    FlashManager::addFlash('error',   'Failed.');
    $this->assertCount(1, $_SESSION['FLASHES']['success']);
    $this->assertCount(1, $_SESSION['FLASHES']['error']);
  }

  public function testAddFlashWithInvalidTypeThrowsInvalidArgumentException(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    FlashManager::addFlash('unknown', 'This should not work.');
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('providerInvalidTypes')]
  public function testAddFlashRejectsInvalidType(string $type): void
  {
    $this->expectException(\InvalidArgumentException::class);
    FlashManager::addFlash($type, 'message');
  }

  public static function providerInvalidTypes(): array
  {
    return [
      'empty string' => [''],
      'notice'       => ['notice'],
      'critical'     => ['critical'],
      'debug'        => ['debug'],
    ];
  }

  // ---- getFlashes() ----

  public function testGetFlashesReturnsEmptyArrayWhenNoFlashes(): void
  {
    $this->assertSame([], FlashManager::getFlashes());
  }

  public function testGetFlashesReturnsStoredMessages(): void
  {
    FlashManager::addFlash('success', 'Great!');
    $flashes = FlashManager::getFlashes();
    $this->assertArrayHasKey('success', $flashes);
    $this->assertContains('Great!', $flashes['success']);
  }

  public function testGetFlashesDoesNotClearOnFirstCall(): void
  {
    FlashManager::addFlash('success', 'Stay.');
    FlashManager::getFlashes(); // first retrieval
    // After the first call FLASHES_DISPLAY_COUNT = 1, flashes still present
    $this->assertTrue(FlashManager::hasFlashes());
  }

  public function testGetFlashesClearsAfterSecondCall(): void
  {
    FlashManager::addFlash('success', 'Go away.');
    FlashManager::getFlashes(); // count = 1
    FlashManager::getFlashes(); // count = 2 → cleared
    $this->assertFalse(FlashManager::hasFlashes());
  }

  public function testGetFlashesReturnsEmptyOnThirdCallAfterClearing(): void
  {
    // getFlashes() captures the flash data before clearing, so it still returns
    // the data on the 2nd call — the session is cleared after that call.
    // A 3rd call finds the session empty and returns [].
    FlashManager::addFlash('info', 'Temporary.');
    FlashManager::getFlashes(); // display count = 1
    FlashManager::getFlashes(); // display count = 2 → session cleared, data still returned
    $third = FlashManager::getFlashes(); // session gone → empty array
    $this->assertSame([], $third);
  }

  // ---- hasFlashes() ----

  public function testHasFlashesReturnsFalseWhenSessionIsEmpty(): void
  {
    $this->assertFalse(FlashManager::hasFlashes());
  }

  public function testHasFlashesReturnsTrueWhenFlashExists(): void
  {
    FlashManager::addFlash('error', 'Oops!');
    $this->assertTrue(FlashManager::hasFlashes());
  }

  public function testHasFlashesReturnsFalseAfterClear(): void
  {
    FlashManager::addFlash('success', 'Done.');
    FlashManager::getFlashes();
    FlashManager::getFlashes(); // clears
    $this->assertFalse(FlashManager::hasFlashes());
  }
}
