<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/helpers.php';

use PHPUnit\Framework\TestCase;

final class helpers_test extends TestCase
{
  public function testToSnakeCase(): void
  {
    $this->assertSame('user_profile', toSnakeCase('UserProfile'));
    $this->assertSame('user_profile_test', toSnakeCase('UserProfileTest'));
    $this->assertSame('user', toSnakeCase('User'));
  }

  public function testToPascalCase(): void
  {
    $this->assertSame('UserProfile', toPascalCase('user_profile'));
    $this->assertSame('UserProfileTest', toPascalCase('user_profile_test'));
    $this->assertSame('User', toPascalCase('user'));
  }

  // ---- t() ----
  // These tests require the yaml PHP extension.  They are automatically
  // skipped when the extension is absent (e.g. outside Docker).

  #[\PHPUnit\Framework\Attributes\RequiresPhpExtension('yaml')]
  public function testTranslationTopLevelKeyReturnsValue(): void
  {
    $this->assertSame('Vytvořit', t('create'));
  }

  #[\PHPUnit\Framework\Attributes\RequiresPhpExtension('yaml')]
  public function testTranslationDotNotationKeyReturnsNestedValue(): void
  {
    $this->assertSame('nemůže být prázdný', t('errors.cannot_be_blank'));
  }

  #[\PHPUnit\Framework\Attributes\RequiresPhpExtension('yaml')]
  public function testTranslationWithParameterInterpolation(): void
  {
    $result = t('pagination.page_info', ['current_page' => 2, 'total_pages' => 5]);
    $this->assertSame('Strana 2 z 5', $result);
  }

  #[\PHPUnit\Framework\Attributes\RequiresPhpExtension('yaml')]
  public function testTranslationWithSingleParameter(): void
  {
    $result = t('errors.must_be_longer_than', ['count' => 3]);
    $this->assertSame('musí být alespoň 3 znaků dlouhý', $result);
  }

  #[\PHPUnit\Framework\Attributes\RequiresPhpExtension('yaml')]
  public function testTranslationUnknownKeyReturnsKey(): void
  {
    $key = 'nonexistent.key.that.does.not.exist';
    $this->assertSame($key, t($key));
  }

  #[\PHPUnit\Framework\Attributes\RequiresPhpExtension('yaml')]
  public function testTranslationUnknownTopLevelKeyReturnsKey(): void
  {
    $this->assertSame('no_such_key', t('no_such_key'));
  }

  // ---- asset_path() ----

  public function testAssetPathForNonExistentFileReturnsPathUnchanged(): void
  {
    $path = '/nonexistent_asset_for_testing_xyz_123abc.css';
    $this->assertSame($path, asset_path($path));
  }

  public function testAssetPathForExistingFileReturnsPathWithVersionSuffix(): void
  {
    // site.webmanifest is a known file shipped with the project
    $result = asset_path('/site.webmanifest');
    $this->assertStringStartsWith('/site.webmanifest?v=', $result);
  }

  public function testAssetPathVersionSuffixIsNumeric(): void
  {
    $result = asset_path('/site.webmanifest');
    $version = substr($result, strrpos($result, '=') + 1);
    $this->assertMatchesRegularExpression('/^\d+$/', $version);
  }
}
