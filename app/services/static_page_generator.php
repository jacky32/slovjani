<?php

/**
 * Generates static HTML snapshots of public pages (posts/ and events/) and
 * saves them under /pregenerated so that index.php can serve them directly
 * without going through PHP routing for each anonymous request.
 *
 * File layout inside /pregenerated:
 *   posts/index_1.html  →  /posts  or /posts?page=1
 *   posts/index_2.html  →  /posts?page=2
 *   posts/5.html        →  /posts/5
 *   events/index_1.html →  /events  or /events?page=1
 *   events/5.html       →  /events/5
 *
 * @package Services
 */
class StaticPageGenerator
{
  /** Absolute path to the pregenerated/ directory. */
  private string $outputDir;

  /**
   * Resolves the absolute path to the pregenerated/ output directory.
   */
  public function __construct()
  {
    // Resolve relative to the project root (two levels up from app/services/)
    $this->outputDir = realpath(__DIR__ . '/../../') . '/pregenerated';
  }

  /**
   * Regenerate every public HTML page for both posts and events.
   * Call this after any admin save/publish/destroy action that may change
   * publicly visible content.
   */
  public function regenerateAll(): void
  {
    Logger::info("StaticPageGenerator: starting full regeneration");
    $this->regeneratePosts();
    $this->regenerateEvents();
    Logger::info("StaticPageGenerator: regeneration complete");
  }

  /**
   * Regenerates all static index and show pages for posts.
   *
   * @return void
   */
  private function regeneratePosts(): void
  {
    // --- index pages ---
    $firstPage   = Post::publiclyVisible()->paginate(1);
    $totalPages  = max(1, $firstPage->total_pages);

    for ($page = 1; $page <= $totalPages; $page++) {
      $this->generatePostsIndex($page);
    }

    // Remove index pages beyond the current total
    $this->pruneIndexPages('posts', $totalPages);

    // --- show pages ---
    for ($page = 1; $page <= $totalPages; $page++) {
      $pagination = Post::publiclyVisible()->paginate($page);
      foreach ($pagination->resources as $post) {
        $this->generatePostShow($post);
      }
    }

    // Remove show pages for posts that are no longer publicly visible
    $this->pruneShowPages('posts', function () use ($totalPages) {
      $ids  = [];
      for ($page = 1; $page <= $totalPages; $page++) {
        foreach (Post::publiclyVisible()->paginate($page)->resources as $post) {
          $ids[] = $post->id;
        }
      }
      return $ids;
    });
  }

  /**
   * Generates (or overwrites) the static index page for a given posts page number.
   *
   * @param int $page Page number to generate.
   * @return void
   */
  private function generatePostsIndex(int $page): void
  {
    $pagination = Post::publiclyVisible()->paginate($page);
    $html = $this->captureRender('posts/index', [
      'posts'      => $pagination->resources,
      'pagination' => $pagination,
    ], 'PostsController');

    $this->save("posts/index_{$page}.html", $html);
  }

  /**
   * Generates (or overwrites) the static show page for a single Post.
   *
   * @param Post $post The post whose page should be regenerated.
   * @return void
   */
  private function generatePostShow(Post $post): void
  {
    $pagination  = Post::publiclyVisible()->paginate(null, $post->id);
    $attachments = $post->attachments->where(['is_publicly_visible' => true])->get();

    $html = $this->captureRender('posts/show', [
      'post'        => $post,
      'posts'       => $pagination->resources,
      'pagination'  => $pagination,
      'attachments' => $attachments,
    ], 'PostsController');

    $this->save("posts/{$post->id}.html", $html);
  }

  /**
   * Regenerates all static index and show pages for events.
   *
   * @return void
   */
  private function regenerateEvents(): void
  {
    // --- index pages ---
    $firstPage  = Event::where(['is_publicly_visible' => true])->paginate(1);
    $totalPages = max(1, $firstPage->total_pages);

    for ($page = 1; $page <= $totalPages; $page++) {
      $this->generateEventsIndex($page);
    }

    $this->pruneIndexPages('events', $totalPages);

    // --- show pages ---
    for ($page = 1; $page <= $totalPages; $page++) {
      $pagination = Event::where(['is_publicly_visible' => true])->paginate($page);
      foreach ($pagination->resources as $event) {
        $this->generateEventShow($event);
      }
    }

    $this->pruneShowPages('events', function () use ($totalPages) {
      $ids = [];
      for ($page = 1; $page <= $totalPages; $page++) {
        foreach (Event::where(['is_publicly_visible' => true])->paginate($page)->resources as $event) {
          $ids[] = $event->id;
        }
      }
      return $ids;
    });
  }

  /**
   * Generates (or overwrites) the static index page for a given events page number.
   *
   * @param int $page Page number to generate.
   * @return void
   */
  private function generateEventsIndex(int $page): void
  {
    $pagination = Event::where(['is_publicly_visible' => true])->paginate($page);
    $html = $this->captureRender('events/index', [
      'events'     => $pagination->resources,
      'pagination' => $pagination,
    ], 'EventsController');

    $this->save("events/index_{$page}.html", $html);
  }

  /**
   * Generates (or overwrites) the static show page for a single Event.
   *
   * @param Event $event The event whose page should be regenerated.
   * @return void
   */
  private function generateEventShow(Event $event): void
  {
    $pagination  = Event::where(['is_publicly_visible' => true])->paginate(null, $event->id);
    $attachments = $event->attachments->where(['is_publicly_visible' => true])->get();

    $html = $this->captureRender('events/show', [
      'event'       => $event,
      'events'      => $pagination->resources,
      'pagination'  => $pagination,
      'attachments' => $attachments,
    ], 'EventsController');

    $this->save("events/{$event->id}.html", $html);
  }

  /**
   * Renders the given view with $data as a guest user and returns the complete
   * HTML string (including the application layout).
   *
   * To prevent the admin's current session flashes from leaking into the
   * generated file, flash state is saved and restored around the render.
   */
  private function captureRender(string $view, array $data, string $controllerName): string
  {
    // Stash current session flash state so it does not appear in the snapshot
    $savedFlashes      = $_SESSION['FLASHES'] ?? null;
    $savedFlashCount   = $_SESSION['FLASHES_DISPLAY_COUNT'] ?? null;
    unset($_SESSION['FLASHES'], $_SESSION['FLASHES_DISPLAY_COUNT']);

    ob_start();
    try {
      $viewManager = new ViewManager(new GuestAuth(), $controllerName);
      $viewManager->render($view, $data);
      // Destroy the object now so __destruct() fires inside this ob block
      // and the layout HTML is captured rather than sent to the response.
      unset($viewManager);
      $html = ob_get_clean();
    } catch (Exception $e) {
      ob_end_clean();
      // Restore flashes before re-throwing
      $this->restoreFlashes($savedFlashes, $savedFlashCount);
      Logger::error("StaticPageGenerator: render failed for $view: " . $e->getMessage());
      throw $e;
    }

    $this->restoreFlashes($savedFlashes, $savedFlashCount);
    return $html;
  }

  /**
   * Restores previously stashed session flash state.
   *
   * @param array|null $flashes  The saved FLASHES value, or null if it was not set.
   * @param int|null   $count    The saved FLASHES_DISPLAY_COUNT value, or null.
   * @return void
   */
  private function restoreFlashes($flashes, $count): void
  {
    if ($flashes !== null) {
      $_SESSION['FLASHES'] = $flashes;
    } else {
      unset($_SESSION['FLASHES']);
    }
    if ($count !== null) {
      $_SESSION['FLASHES_DISPLAY_COUNT'] = $count;
    } else {
      unset($_SESSION['FLASHES_DISPLAY_COUNT']);
    }
  }

  // -------------------------------------------------------------------------
  // File system helpers
  // -------------------------------------------------------------------------

  /** Write $html to $relativePath inside the pregenerated/ directory. */
  private function save(string $relativePath, string $html): void
  {
    $fullPath = $this->outputDir . '/' . $relativePath;
    $dir = dirname($fullPath);

    if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
    }

    file_put_contents($fullPath, $html);
    Logger::debug("StaticPageGenerator: saved pregenerated/$relativePath");
  }

  /**
   * Delete all index_N.html files where N > $totalPages for the given resource.
   * Called after regeneration to remove stale pagination files.
   */
  private function pruneIndexPages(string $resource, int $totalPages): void
  {
    $dir = $this->outputDir . '/' . $resource;
    if (!is_dir($dir)) {
      return;
    }

    foreach (glob($dir . '/index_*.html') ?: [] as $file) {
      if (preg_match('/index_(\d+)\.html$/', $file, $m) && (int) $m[1] > $totalPages) {
        unlink($file);
        Logger::debug("StaticPageGenerator: pruned $file");
      }
    }
  }

  /**
   * Delete show pages (numeric ID files) for resources that are no longer
   * publicly visible.
   *
   * @param string   $resource    'posts' or 'events'
   * @param callable $getIds      Returns int[] of currently visible IDs
   */
  private function pruneShowPages(string $resource, callable $getIds): void
  {
    $dir = $this->outputDir . '/' . $resource;
    if (!is_dir($dir)) {
      return;
    }

    $visibleIds = $getIds();

    foreach (glob($dir . '/[0-9]*.html') ?: [] as $file) {
      $id = (int) basename($file, '.html');
      if (!in_array($id, $visibleIds, true)) {
        unlink($file);
        Logger::debug("StaticPageGenerator: pruned $file");
      }
    }
  }
}
