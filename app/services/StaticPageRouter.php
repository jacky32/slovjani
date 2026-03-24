<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Resolves incoming HTTP requests to pregenerated static HTML files.
 *
 * Responsibilities:
 *  - Map a request path (and optional page number) to the expected file path
 *    inside the pregenerated/ directory.
 *  - Apply the login gate: logged-in users are never sent a static file.
 *  - Return the absolute path of the file to serve, or null when PHP routing
 *    should handle the request normally.
 *
 * @package Services
 */
class StaticPageRouter
{
  /** Absolute path to the pregenerated/ directory. */
  private string $baseDir;

  /**
   * @param string $baseDir Absolute path to the pregenerated/ directory.
   */
  public function __construct(string $baseDir)
  {
    $this->baseDir = rtrim($baseDir, '/');
  }

  /**
   * Determine which pregenerated file should be served for a given request,
   * taking the HTTP method and login state into account.
   *
   * Returns the absolute path of the pregenerated file when ALL of the
   * following are true:
   *  - The HTTP method is GET.
   *  - The user is not logged in.
   *  - The request path matches a public resource route (posts or events).
   *  - The corresponding file exists on disk.
   *
   * Returns null in every other case so that normal PHP routing takes over.
   *
   * @param string $method      HTTP method (e.g., 'GET', 'POST')
   * @param bool   $isLoggedIn  Whether the current user is authenticated
   * @param string $requestPath URL path component (e.g., '/posts', '/posts/5')
   * @param int    $page        Current page number (≥ 1, from the query string)
   * @return string|null        Absolute path to serve, or null to fall through
   */
  public function resolve(string $method, bool $isLoggedIn, string $requestPath, int $page = 1): ?string
  {
    if ($method !== 'GET' || $isLoggedIn) {
      return null;
    }

    $candidate = $this->candidatePath($requestPath, $page);
    if ($candidate === null) {
      return null;
    }

    return is_file($candidate) ? $candidate : null;
  }

  /**
   * Map a request path to the expected file path without checking whether
   * the file actually exists on disk. Useful for testing the path logic
   * in isolation.
   *
   * @param string $requestPath URL path component
   * @param int    $page        Current page number (≥ 1)
   * @return string|null        Candidate absolute path, or null if the path
   *                            does not match any pregenerated route.
   */
  public function candidatePath(string $requestPath, int $page = 1): ?string
  {
    $page = max(1, $page);

    if (preg_match('#^/posts/(\d+)$#', $requestPath, $matches)) {
      return "{$this->baseDir}/posts/{$matches[1]}.html";
    }

    if ($requestPath === '/posts') {
      return "{$this->baseDir}/posts/index_{$page}.html";
    }

    if (preg_match('#^/events/(\d+)$#', $requestPath, $matches)) {
      return "{$this->baseDir}/events/{$matches[1]}.html";
    }

    if ($requestPath === '/events') {
      return "{$this->baseDir}/events/index_{$page}.html";
    }

    return null;
  }
}

class_alias(__NAMESPACE__ . '\\StaticPageRouter', 'StaticPageRouter');
