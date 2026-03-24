<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attachment;

/**
 * Resolves editor media source aliases to existing attachment routes.
 *
 * Supported source forms:
 * - `attachment:<id>`
 * - `attachment:<token>`
 * - `attachment:<file_name>`
 * - `attachment:<visible_name>`
 * - `<id>` or `<token>` (numeric/token shorthand)
 * - `<file_name>` / `<visible_name>` (simple name shorthand)
 *
 * @package Services
 */
class AttachmentMarkupMediaSourceResolver
{
  private string $resourceType;
  private int $resourceId;
  private string $resourcePath;
  private bool $adminContext;
  private bool $publicOnly;

  /**
   * @param string $resourceType Parent resource class, e.g. Post::class.
   * @param int|string $resourceId Parent resource ID.
   * @param string $resourcePath Parent resource route segment, e.g. `posts`.
   * @param bool $adminContext True when links should point to /admin routes.
   * @param bool $publicOnly True to resolve only publicly visible attachments.
   */
  public function __construct(string $resourceType, $resourceId, string $resourcePath, bool $adminContext = false, bool $publicOnly = false)
  {
    $this->resourceType = $resourceType;
    $this->resourceId = (int) $resourceId;
    $this->resourcePath = $resourcePath;
    $this->adminContext = $adminContext;
    $this->publicOnly = $publicOnly;
  }

  /**
   * @param string $source Raw source from //@source@caption.
   * @return string|null Resolved attachment URL or null when unresolved.
   */
  public function __invoke(string $source): ?string
  {
    $source = trim($source);
    if ($source === '' || $this->isNonResolvableSource($source)) {
      return null;
    }

    $identifier = $this->extractIdentifier($source);
    if ($identifier === '') {
      return null;
    }

    $attachment = $this->findAttachment($identifier);
    if (!$attachment || !isset($attachment->id)) {
      return null;
    }

    return $this->buildAttachmentUrl((int) $attachment->id);
  }

  /**
   * Non-resolvable sources should keep original parser behavior.
   */
  private function isNonResolvableSource(string $source): bool
  {
    if (filter_var($source, FILTER_VALIDATE_URL)) {
      return true;
    }

    if (str_starts_with($source, '/')) {
      return true;
    }

    return false;
  }

  /**
   * Removes explicit `attachment:` prefix when present.
   */
  private function extractIdentifier(string $source): string
  {
    if (str_starts_with($source, 'attachment:')) {
      return trim(substr($source, strlen('attachment:')));
    }

    return $source;
  }

  /**
   * Tries multiple lookup strategies against Attachment model.
   */
  private function findAttachment(string $identifier): ?object
  {
    if (ctype_digit($identifier)) {
      $match = $this->findBy(['id' => (int) $identifier]);
      if ($match !== null) {
        return $match;
      }
    }

    if (preg_match('/^[a-f0-9]{32}$/i', $identifier)) {
      $match = $this->findBy(['token' => $identifier]);
      if ($match !== null) {
        return $match;
      }
    }

    $match = $this->findBy(['file_name' => $identifier]);
    if ($match !== null) {
      return $match;
    }

    return $this->findBy(['visible_name' => $identifier]);
  }

  /**
   * @param array $conditions Additional where conditions.
   */
  private function findBy(array $conditions): ?object
  {
    $baseConditions = [
      'resource_type' => $this->resourceType,
      'resource_id' => $this->resourceId,
    ];

    if ($this->publicOnly) {
      $baseConditions['is_publicly_visible'] = true;
    }

    return Attachment::where(array_merge($baseConditions, $conditions))->first();
  }

  private function buildAttachmentUrl(int $attachmentId): string
  {
    $base = $this->adminContext ? '/admin' : '';
    return $base . '/' . $this->resourcePath . '/' . $this->resourceId . '/attachments/' . $attachmentId;
  }
}

class_alias(__NAMESPACE__ . '\\AttachmentMarkupMediaSourceResolver', 'AttachmentMarkupMediaSourceResolver');
