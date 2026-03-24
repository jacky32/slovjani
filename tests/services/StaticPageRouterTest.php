<?php

declare(strict_types=1);

require __DIR__ . '/../../app/services/StaticPageRouter.php';

use PHPUnit\Framework\TestCase;

/**
 * Tests for StaticPageRouter.
 *
 * The suite is organised into two groups:
 *   1. candidatePath() – pure path-to-filename mapping (no filesystem access).
 *   2. resolve()       – full gate: method + login state + file existence.
 *
 * Tests in group 2 use a temporary directory populated with empty stub files
 * so that is_file() calls inside resolve() return predictable results.
 */
final class StaticPageRouterTest extends TestCase
{
  /** Absolute path of the temporary pregenerated/ directory for the current test. */
  private string $tmpDir;

  protected function setUp(): void
  {
    $this->tmpDir = sys_get_temp_dir() . '/static_page_router_test_' . uniqid();
    mkdir($this->tmpDir . '/posts',  0755, true);
    mkdir($this->tmpDir . '/events', 0755, true);
  }

  protected function tearDown(): void
  {
    // Recursively remove temp dir and all stub files.
    foreach (glob($this->tmpDir . '/**/*') ?: [] as $f) {
      is_file($f) && unlink($f);
    }
    foreach (glob($this->tmpDir . '/*') ?: [] as $f) {
      is_dir($f) ? rmdir($f) : unlink($f);
    }
    rmdir($this->tmpDir);
  }

  /** Create an empty stub file inside the temp pregenerated directory. */
  private function touchFile(string $relativePath): string
  {
    $abs = $this->tmpDir . '/' . $relativePath;
    file_put_contents($abs, '<html>stub</html>');
    return $abs;
  }

  private function router(): StaticPageRouter
  {
    return new StaticPageRouter($this->tmpDir);
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('providerPostsIndexPaths')]
  public function testCandidatePathPostsIndex(int $page, string $expectedSuffix): void
  {
    $path = $this->router()->candidatePath('/posts', $page);
    $this->assertStringEndsWith($expectedSuffix, $path);
  }

  public static function providerPostsIndexPaths(): array
  {
    return [
      'page 1'  => [1, 'posts/index_1.html'],
      'page 2'  => [2, 'posts/index_2.html'],
      'page 10' => [10, 'posts/index_10.html'],
    ];
  }

  public function testCandidatePathPostsIndexClampsBelowOne(): void
  {
    // Page 0 or negative should be treated as page 1
    $this->assertStringEndsWith('posts/index_1.html', $this->router()->candidatePath('/posts', 0));
    $this->assertStringEndsWith('posts/index_1.html', $this->router()->candidatePath('/posts', -5));
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('providerPostShowPaths')]
  public function testCandidatePathPostShow(string $requestPath, string $expectedSuffix): void
  {
    $path = $this->router()->candidatePath($requestPath);
    $this->assertStringEndsWith($expectedSuffix, $path);
  }

  public static function providerPostShowPaths(): array
  {
    return [
      'post 1'   => ['/posts/1',   'posts/1.html'],
      'post 42'  => ['/posts/42',  'posts/42.html'],
      'post 999' => ['/posts/999', 'posts/999.html'],
    ];
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('providerEventsIndexPaths')]
  public function testCandidatePathEventsIndex(int $page, string $expectedSuffix): void
  {
    $path = $this->router()->candidatePath('/events', $page);
    $this->assertStringEndsWith($expectedSuffix, $path);
  }

  public static function providerEventsIndexPaths(): array
  {
    return [
      'page 1'  => [1, 'events/index_1.html'],
      'page 3'  => [3, 'events/index_3.html'],
    ];
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('providerEventShowPaths')]
  public function testCandidatePathEventShow(string $requestPath, string $expectedSuffix): void
  {
    $path = $this->router()->candidatePath($requestPath);
    $this->assertStringEndsWith($expectedSuffix, $path);
  }

  public static function providerEventShowPaths(): array
  {
    return [
      'event 1'  => ['/events/1',  'events/1.html'],
      'event 77' => ['/events/77', 'events/77.html'],
    ];
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('providerNonPregenPaths')]
  public function testCandidatePathReturnsNullForNonPregenRoutes(string $requestPath): void
  {
    $this->assertNull($this->router()->candidatePath($requestPath));
  }

  public static function providerNonPregenPaths(): array
  {
    return [
      'root'          => ['/'],
      'home'          => ['/home'],
      'admin posts'   => ['/admin/posts'],
      'admin events'  => ['/admin/events'],
      'sessions'      => ['/login'],
      'posts prefix'  => ['/posts/5/edit'],
      'events prefix' => ['/events/5/attachments'],
    ];
  }

  // ---- Posts index ---

  public function testResolveServesPostsIndexPageOneForGuest(): void
  {
    $this->touchFile('posts/index_1.html');
    $result = $this->router()->resolve('GET', false, '/posts', 1);
    $this->assertNotNull($result);
    $this->assertStringEndsWith('posts/index_1.html', $result);
  }

  public function testResolveServesPostsIndexPageTwoForGuest(): void
  {
    $this->touchFile('posts/index_2.html');
    $result = $this->router()->resolve('GET', false, '/posts', 2);
    $this->assertNotNull($result);
    $this->assertStringEndsWith('posts/index_2.html', $result);
  }

  public function testResolveReturnsNullForPostsIndexWhenFileIsMissing(): void
  {
    // No file created → must fall through to PHP routing
    $result = $this->router()->resolve('GET', false, '/posts', 1);
    $this->assertNull($result);
  }

  // ---- Posts show ---

  public function testResolveServesPostShowForGuest(): void
  {
    $this->touchFile('posts/5.html');
    $result = $this->router()->resolve('GET', false, '/posts/5');
    $this->assertNotNull($result);
    $this->assertStringEndsWith('posts/5.html', $result);
  }

  public function testResolveReturnsNullForPostShowWhenFileIsMissing(): void
  {
    $result = $this->router()->resolve('GET', false, '/posts/99');
    $this->assertNull($result);
  }

  // ---- Events index ---

  public function testResolveServesEventsIndexForGuest(): void
  {
    $this->touchFile('events/index_1.html');
    $result = $this->router()->resolve('GET', false, '/events', 1);
    $this->assertNotNull($result);
    $this->assertStringEndsWith('events/index_1.html', $result);
  }

  public function testResolveServesEventsIndexPageThreeForGuest(): void
  {
    $this->touchFile('events/index_3.html');
    $result = $this->router()->resolve('GET', false, '/events', 3);
    $this->assertNotNull($result);
    $this->assertStringEndsWith('events/index_3.html', $result);
  }

  // ---- Events show ---

  public function testResolveServesEventShowForGuest(): void
  {
    $this->touchFile('events/7.html');
    $result = $this->router()->resolve('GET', false, '/events/7');
    $this->assertNotNull($result);
    $this->assertStringEndsWith('events/7.html', $result);
  }

  // ---- Login gate ---

  public function testResolveReturnsNullForLoggedInUserEvenWhenFileExists(): void
  {
    $this->touchFile('posts/index_1.html');
    $this->touchFile('posts/5.html');
    $this->touchFile('events/index_1.html');
    $this->touchFile('events/7.html');

    $this->assertNull($this->router()->resolve('GET', true, '/posts', 1),   '/posts logged-in');
    $this->assertNull($this->router()->resolve('GET', true, '/posts/5'),     '/posts/5 logged-in');
    $this->assertNull($this->router()->resolve('GET', true, '/events', 1),  '/events logged-in');
    $this->assertNull($this->router()->resolve('GET', true, '/events/7'),    '/events/7 logged-in');
  }

  // ---- HTTP method gate ---

  #[\PHPUnit\Framework\Attributes\DataProvider('providerNonGetMethods')]
  public function testResolveReturnsNullForNonGetMethods(string $method): void
  {
    $this->touchFile('posts/index_1.html');
    $result = $this->router()->resolve($method, false, '/posts', 1);
    $this->assertNull($result, "$method should never serve a pregen file");
  }

  public static function providerNonGetMethods(): array
  {
    return [
      'POST'   => ['POST'],
      'PUT'    => ['PUT'],
      'PATCH'  => ['PATCH'],
      'DELETE' => ['DELETE'],
    ];
  }

  // ---- Non-pregenerable routes ---

  public function testResolveReturnsNullForRoutesOutsidePregenScope(): void
  {
    $this->assertNull($this->router()->resolve('GET', false, '/'));
    $this->assertNull($this->router()->resolve('GET', false, '/admin/posts'));
    $this->assertNull($this->router()->resolve('GET', false, '/login'));
  }

  // ---- Returned path is the actual file path ---

  public function testResolveReturnedPathIsReadable(): void
  {
    $abs = $this->touchFile('posts/42.html');
    $result = $this->router()->resolve('GET', false, '/posts/42');
    $this->assertSame($abs, $result);
    $this->assertFileExists($result);
    $this->assertStringEqualsFile($result, '<html>stub</html>');
  }
}
