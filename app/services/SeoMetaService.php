<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Builds SEO metadata for HTML head tags.
 *
 * @package Services
 */
class SeoMetaService
{
  /**
   * Build SEO metadata values from request and page context.
   *
   * @return array<string, string>
   */
  public function build(
    string $siteName,
    string $defaultDescription,
    ?string $pageTitle,
    string $pageContent,
    string $requestUri,
    string $host,
    bool $isHttps,
    bool $isShowPage = false,
    ?string $explicitDescription = null,
    ?string $showDescriptionSource = null
  ): array {
    $safeSiteName = trim($siteName);
    $rawTitle = trim((string) $pageTitle);
    $resolvedPageTitle = $rawTitle !== '' ? $rawTitle : $safeSiteName;
    $title = $resolvedPageTitle === $safeSiteName ? $safeSiteName : $resolvedPageTitle . ' | ' . $safeSiteName;

    $canonicalPath = (string) (strtok($requestUri, '?') ?: '/');
    $scheme = $isHttps ? 'https' : 'http';
    $canonicalUrl = $host !== '' ? $scheme . '://' . $host . $canonicalPath : $canonicalPath;
    $ogImage = $host !== '' ? $scheme . '://' . $host . '/apple-touch-icon.png' : '/apple-touch-icon.png';

    $description = $this->resolveDescription(
      defaultDescription: $defaultDescription,
      pageContent: $pageContent,
      title: $resolvedPageTitle,
      explicitDescription: $explicitDescription,
      isShowPage: $isShowPage,
      showDescriptionSource: $showDescriptionSource
    );

    $isPrivatePath = preg_match('#^/(admin|sessions)(/|$)#', $canonicalPath) === 1;
    $robots = $isPrivatePath ? 'noindex, nofollow' : 'index, follow';

    return [
      'title' => $title,
      'site_name' => $safeSiteName,
      'description' => $description,
      'canonical_url' => $canonicalUrl,
      'og_image' => $ogImage,
      'robots' => $robots,
    ];
  }

  private function resolveDescription(
    string $defaultDescription,
    string $pageContent,
    string $title,
    ?string $explicitDescription,
    bool $isShowPage,
    ?string $showDescriptionSource
  ): string {
    $explicit = trim((string) $explicitDescription);
    if ($explicit !== '') {
      return $this->truncate($explicit);
    }

    $normalizedDefault = trim($defaultDescription);
    $showSource = $this->normalizeText((string) $showDescriptionSource);

    if ($isShowPage) {
      $base = trim($title);
      if ($showSource !== '') {
        return $this->truncate($base . ' - ' . $showSource);
      }
      return $this->truncate($base . ' - ' . $normalizedDefault);
    }

    $contentText = $this->normalizeText($pageContent);
    if ($contentText !== '') {
      return $this->truncate($contentText);
    }

    return $this->truncate($normalizedDefault);
  }

  private function normalizeText(string $text): string
  {
    return trim((string) preg_replace('/\s+/', ' ', strip_tags($text)));
  }

  private function truncate(string $text, int $limit = 160): string
  {
    $value = trim($text);
    if ($value === '') {
      return '';
    }

    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    if ($length <= $limit) {
      return $value;
    }

    $slice = function_exists('mb_substr') ? mb_substr($value, 0, $limit) : substr($value, 0, $limit);
    return rtrim($slice) . '...';
  }
}

class_alias(__NAMESPACE__ . '\\SeoMetaService', 'SeoMetaService');
