<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Converts editor markup syntax into HTML.
 *
 * Supported block markers (must start with //):
 * - //text          : normal paragraph
 * - //*text         : unordered list item
 * - //#text         : ordered list item
 * - //=text         : centered paragraph
 * - ///text         : small heading
 * - //@src@caption  : image block or YouTube embed block
 * - //.             : end marker (parsing stops)
 *
 * @package Services
 */
class EditorMarkupParser
{
  private const TOKEN_BREAK = '__EDITOR_TOKEN_BREAK__';
  private const TOKEN_HASH = '__EDITOR_TOKEN_HASH__';
  private const TOKEN_LT = '__EDITOR_TOKEN_LT__';
  private const TOKEN_GT = '__EDITOR_TOKEN_GT__';
  private const TOKEN_LB = '__EDITOR_TOKEN_LB__';
  private const TOKEN_RB = '__EDITOR_TOKEN_RB__';
  private const TOKEN_LC = '__EDITOR_TOKEN_LC__';
  private const TOKEN_RC = '__EDITOR_TOKEN_RC__';
  private const TOKEN_SLASH = '__EDITOR_TOKEN_SLASH__';
  private const TOKEN_BACKSLASH = '__EDITOR_TOKEN_BACKSLASH__';

  /**
   * Optional callback used to resolve media sources (e.g. attachment aliases)
   * before image/embed rendering.
   *
   * Signature: function(string $source): ?string
   *
   * @var callable|null
   */
  private $mediaSourceResolver;

  /**
   * @param callable|null $mediaSourceResolver Optional source resolver callback.
   */
  public function __construct(?callable $mediaSourceResolver = null)
  {
    $this->mediaSourceResolver = $mediaSourceResolver;
  }

  /**
   * Parses raw editor markup into sanitized HTML.
   *
   * @param string $input Raw editor text.
   * @return string Parsed HTML output.
   */
  public function parse(string $input): string
  {
    $lines = $this->coalesceWrappedLines($input);

    $html = [];
    $activeListType = null; // 'ul' | 'ol' | null

    foreach ($lines as $line) {
      if ($line === '//.') {
        break;
      }

      if (!str_starts_with($line, '//')) {
        continue;
      }

      $body = substr($line, 2);

      if (str_starts_with($body, '*')) {
        $activeListType = $this->openListIfNeeded($html, $activeListType, 'ul');
        $html[] = $this->parseBulletedParagraph(substr($body, 1));
        continue;
      }

      if (str_starts_with($body, '#')) {
        $activeListType = $this->openListIfNeeded($html, $activeListType, 'ol');
        $html[] = $this->parseNumberedParagraph(substr($body, 1));
        continue;
      }

      if (str_starts_with($body, '=')) {
        $this->closeListIfOpen($html, $activeListType);
        $html[] = $this->parseCenteredParagraph(substr($body, 1));
        continue;
      }

      if (str_starts_with($body, '/')) {
        $this->closeListIfOpen($html, $activeListType);
        $html[] = $this->parseSmallHeading(substr($body, 1));
        continue;
      }

      if (str_starts_with($body, '@')) {
        $this->closeListIfOpen($html, $activeListType);
        $html[] = $this->parseMediaParagraph($body);
        continue;
      }

      $this->closeListIfOpen($html, $activeListType);
      $html[] = $this->parseNormalParagraph($body);
    }

    $this->closeListIfOpen($html, $activeListType);

    return implode("\n", array_filter($html, static fn($part) => $part !== ''));
  }

  /**
   * Joins wrapped physical lines into logical lines.
   * If a line does not start with //, it is treated as continuation of the
   * previous logical line.
   */
  private function coalesceWrappedLines(string $input): array
  {
    $rawLines = preg_split('/\R/', str_replace(["\r\n", "\r"], "\n", trim($input)));
    $logicalLines = [];

    foreach ($rawLines as $rawLine) {
      $line = trim($rawLine);
      if ($line === '') {
        continue;
      }

      if (str_starts_with($line, '//') || $logicalLines === []) {
        $logicalLines[] = $line;
      } else {
        $lastIndex = count($logicalLines) - 1;
        $logicalLines[$lastIndex] .= ' ' . $line;
      }
    }

    return $logicalLines;
  }

  /**
   * Parses a normal paragraph block.
   *
   * @param string $content Paragraph content.
   * @return string HTML paragraph.
   */
  private function parseNormalParagraph(string $content): string
  {
    return '<p>' . $this->parseInline($content) . '</p>';
  }

  /**
   * Parses one unordered-list item.
   *
   * @param string $content List item content.
   * @return string HTML list item.
   */
  private function parseBulletedParagraph(string $content): string
  {
    return '<li>' . $this->parseInline($content) . '</li>';
  }

  /**
   * Parses one ordered-list item.
   *
   * @param string $content List item content.
   * @return string HTML list item.
   */
  private function parseNumberedParagraph(string $content): string
  {
    return '<li>' . $this->parseInline($content) . '</li>';
  }

  /**
   * Parses a centered paragraph block.
   *
   * @param string $content Paragraph content.
   * @return string Centered HTML paragraph.
   */
  private function parseCenteredParagraph(string $content): string
  {
    return '<p class="text-center">' . $this->parseInline($content) . '</p>';
  }

  /**
   * Parses a small heading block.
   *
   * @param string $content Heading content.
   * @return string HTML heading.
   */
  private function parseSmallHeading(string $content): string
  {
    return '<h2 class="small-heading text-center">' . $this->parseInline($content) . '</h2>';
  }

  /**
   * Parses media block syntax (image/YouTube embed).
   *
   * @param string $content Raw media block expression.
   * @return string HTML media markup.
   */
  private function parseMediaParagraph(string $content): string
  {
    if (!preg_match('/^@([^@]+)@(.*)$/', $content, $matches)) {
      return '<p>' . $this->parseInline($content) . '</p>';
    }

    $source = trim($matches[1]);
    $source = $this->resolveMediaSource($source);
    $caption = $this->parseInline($matches[2]);

    if ($this->isYouTubeEmbedSource($source)) {
      return $this->parseYouTubeEmbed($source, $caption);
    }

    return $this->parseImageFigure($source, $caption);
  }

  /**
   * Resolves media source aliases through an optional callback.
   *
   * @param string $source Raw source value from markup.
   * @return string Resolved source, or original source when unresolved.
   */
  private function resolveMediaSource(string $source): string
  {
    if (!is_callable($this->mediaSourceResolver)) {
      return $source;
    }

    $resolved = ($this->mediaSourceResolver)($source);
    if (!is_string($resolved)) {
      return $source;
    }

    $resolved = trim($resolved);
    return $resolved !== '' ? $resolved : $source;
  }

  /**
   * Builds HTML figure markup for an image.
   *
   * @param string $source Image source URL/path.
   * @param string $caption Parsed caption HTML.
   * @return string HTML figure.
   */
  private function parseImageFigure(string $source, string $caption): string
  {
    $safeSource = htmlspecialchars($source, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<figure><img src="' . $safeSource . '" alt="' . strip_tags($caption) . '"><figcaption>' . $caption . '</figcaption></figure>';
  }

  /**
   * Builds HTML figure markup for a YouTube embed.
   *
   * @param string $source YouTube embed URL.
   * @param string $caption Parsed caption HTML.
   * @return string HTML embed figure.
   */
  private function parseYouTubeEmbed(string $source, string $caption): string
  {
    $safeSource = htmlspecialchars($source, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<figure class="video-embed"><iframe src="' . $safeSource . '" title="YouTube video" allowfullscreen loading="lazy"></iframe><figcaption>' . $caption . '</figcaption></figure>';
  }

  /**
   * Validates whether a media source is a supported YouTube embed URL.
   *
   * @param string $source Candidate source.
   * @return bool True when the source matches the embed URL pattern.
   */
  private function isYouTubeEmbedSource(string $source): bool
  {
    return (bool) preg_match('/^https:\/\/www\.youtube\.com\/embed\/[A-Za-z0-9_-]+$/', $source);
  }

  /**
   * Inline parser pipeline.
   */
  private function parseInline(string $content): string
  {
    $content = trim($content);
    if ($content === '') {
      return '';
    }

    $content = $this->parseEscapedTokens($content);
    $content = htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $content = $this->parseLineBreakToken($content);
    $content = $this->parseCurlyReferenceToken($content);
    $content = $this->parseBoldToken($content);
    $content = $this->parseItalicToken($content);

    return $this->restoreEscapedTokens($content);
  }

  /**
   * Replaces escaped control sequences with neutral placeholders.
   *
   * Supports escaped marker forms used in source texts, e.g. `\<`, `\/<`,
   * and `/\<`.
   *
   * @param string $content Raw inline content.
   * @return string Content with placeholder tokens.
   */
  private function parseEscapedTokens(string $content): string
  {
    // `\/<`-style forms should render as literal `\<` (not as a line break).
    $content = preg_replace_callback('~\\\\/(?:\\\\)?([#<>\[\]\{\}/])~', function (array $matches): string {
      $token = match ($matches[1]) {
        '#' => self::TOKEN_HASH,
        '<' => self::TOKEN_LT,
        '>' => self::TOKEN_GT,
        '[' => self::TOKEN_LB,
        ']' => self::TOKEN_RB,
        '{' => self::TOKEN_LC,
        '}' => self::TOKEN_RC,
        '/' => self::TOKEN_SLASH,
        default => $matches[0],
      };

      return self::TOKEN_BACKSLASH . $token;
    }, $content) ?? $content;

    // Handle double-backslash escaped symbols first (e.g. `\\<`) so they do
    // not get interpreted as a line-break marker (`\\`).
    $content = preg_replace_callback('~\\\\\\\\([#<>\[\]\{\}/])~', function (array $matches): string {
      return match ($matches[1]) {
        '#' => self::TOKEN_HASH,
        '<' => self::TOKEN_LT,
        '>' => self::TOKEN_GT,
        '[' => self::TOKEN_LB,
        ']' => self::TOKEN_RB,
        '{' => self::TOKEN_LC,
        '}' => self::TOKEN_RC,
        '/' => self::TOKEN_SLASH,
        default => $matches[0],
      };
    }, $content) ?? $content;

    $content = preg_replace_callback('~\\\\([#<>\[\]\{\}])~', function (array $matches): string {
      return match ($matches[1]) {
        '#' => self::TOKEN_HASH,
        '<' => self::TOKEN_LT,
        '>' => self::TOKEN_GT,
        '[' => self::TOKEN_LB,
        ']' => self::TOKEN_RB,
        '{' => self::TOKEN_LC,
        '}' => self::TOKEN_RC,
        default => $matches[0],
      };
    }, $content) ?? $content;

    $content = preg_replace_callback('~/\\\\(?:\\\\)?([#<>\[\]\{\}/])~', function (array $matches): string {
      return match ($matches[1]) {
        '#' => self::TOKEN_HASH,
        '<' => self::TOKEN_LT,
        '>' => self::TOKEN_GT,
        '[' => self::TOKEN_LB,
        ']' => self::TOKEN_RB,
        '{' => self::TOKEN_LC,
        '}' => self::TOKEN_RC,
        '/' => self::TOKEN_SLASH,
        default => $matches[0],
      };
    }, $content) ?? $content;

    return str_replace(['\\/', '\\\\'], [self::TOKEN_BACKSLASH, self::TOKEN_BREAK], $content);
  }

  /**
   * Converts line-break placeholder token to HTML break tags.
   *
   * @param string $content Inline content.
   * @return string Inline content with <br> tags.
   */
  private function parseLineBreakToken(string $content): string
  {
    return str_replace(self::TOKEN_BREAK, '<br>', $content);
  }

  /**
   * Converts `[text]` to `<strong>text</strong>`.
   *
   * @param string $content Inline content.
   * @return string Content with bold markup.
   */
  private function parseBoldToken(string $content): string
  {
    return preg_replace('/\[(.+?)\]/s', '<strong>$1</strong>', $content) ?? $content;
  }

  /**
   * Converts escaped `<text>` markers to `<em>text</em>`.
   *
   * @param string $content Inline content.
   * @return string Content with italic markup.
   */
  private function parseItalicToken(string $content): string
  {
    return preg_replace('/&lt;(.+?)&gt;/s', '<em>$1</em>', $content) ?? $content;
  }

  /**
   * Parses `{...}` references as email/URL links when valid.
   *
   * @param string $content Inline content.
   * @return string Content with parsed references.
   */
  private function parseCurlyReferenceToken(string $content): string
  {
    $self = $this;

    return preg_replace_callback('/\{([^{}]+)\}/', static function (array $matches) use ($self): string {
      return $self->parseCurlyReference($matches[1]);
    }, $content) ?? $content;
  }

  /**
   * Parses one curly-brace reference payload.
   *
   * @param string $rawReference Raw content inside braces.
   * @return string HTML-safe replacement.
   */
  private function parseCurlyReference(string $rawReference): string
  {
    $decoded = html_entity_decode(trim($rawReference), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    if (filter_var($decoded, FILTER_VALIDATE_EMAIL)) {
      $safe = htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      return '<a href="mailto:' . $safe . '">' . $safe . '</a>';
    }

    if (filter_var($decoded, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $decoded)) {
      $safe = htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      return '<a href="' . $safe . '" target="_blank" rel="noopener noreferrer">' . $safe . '</a>';
    }

    // Keep unknown curly content as plain text.
    return '{' . htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '}';
  }

  /**
   * Restores placeholder tokens back to literal characters.
   *
   * @param string $content Inline content with tokens.
   * @return string Restored inline content.
   */
  private function restoreEscapedTokens(string $content): string
  {
    $replacements = [
      self::TOKEN_BACKSLASH => '\\',
      self::TOKEN_HASH => '#',
      self::TOKEN_LT => '&lt;',
      self::TOKEN_GT => '&gt;',
      self::TOKEN_LB => '[',
      self::TOKEN_RB => ']',
      self::TOKEN_LC => '{',
      self::TOKEN_RC => '}',
      self::TOKEN_SLASH => '/',
    ];

    return strtr($content, $replacements);
  }

  /**
   * Opens target list type if needed and closes previous list when switching.
   *
   * @param array $html Output HTML buffer.
   * @param string|null $activeListType Current active list type.
   * @param string $targetType Target list type (`ul` or `ol`).
   * @return string Active list type after update.
   */
  private function openListIfNeeded(array &$html, ?string $activeListType, string $targetType): string
  {
    if ($activeListType === $targetType) {
      return $activeListType;
    }

    if ($activeListType !== null) {
      $html[] = '</' . $activeListType . '>';
    }

    $html[] = '<' . $targetType . '>';
    return $targetType;
  }

  /**
   * Closes the currently open list if one is active.
   *
   * @param array $html Output HTML buffer.
   * @param string|null $activeListType Active list type by reference.
   */
  private function closeListIfOpen(array &$html, ?string &$activeListType): void
  {
    if ($activeListType !== null) {
      $html[] = '</' . $activeListType . '>';
      $activeListType = null;
    }
  }
}

class_alias(__NAMESPACE__ . '\\EditorMarkupParser', 'EditorMarkupParser');
