<!doctype html>
<html lang="cs">

<head>
  <?php
  $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
  $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
  $canonicalPath = (string) (strtok($requestUri, '?') ?: '/');
  $isPublicPage = preg_match('#^/(admin|sessions)(/|$)#', $canonicalPath) !== 1;
  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
  $title = (string) ($this->getTitle() ?? t('app.default_title'));
  $seoMeta = null;
  if ($isPublicPage) {
    $seoMeta = (new App\Services\SeoMetaService())->build(
      siteName: t('app.default_title'),
      defaultDescription: t('app.default_description'),
      pageTitle: $title,
      pageContent: (string) ($this->content ?? ''),
      requestUri: $requestUri,
      host: $host,
      isHttps: $isHttps,
      isShowPage: $this->isShowPage(),
      explicitDescription: $this->getMetaDescription(),
      showDescriptionSource: $this->getMetaDescriptionSource()
    );
    $title = (string) $seoMeta['title'];
  }
  ?>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <?php if ($isPublicPage && is_array($seoMeta)): ?>
    <meta name="description" content="<?= htmlspecialchars($seoMeta['description'], ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="robots" content="<?= htmlspecialchars($seoMeta['robots'], ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="cs_CZ" />
    <meta property="og:site_name" content="<?= htmlspecialchars($seoMeta['site_name'], ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:title" content="<?= htmlspecialchars($seoMeta['title'], ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($seoMeta['description'], ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:url" content="<?= htmlspecialchars($seoMeta['canonical_url'], ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($seoMeta['og_image'], ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="<?= htmlspecialchars($seoMeta['title'], ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($seoMeta['description'], ENT_QUOTES, 'UTF-8') ?>" />
    <link rel="canonical" href="<?= htmlspecialchars($seoMeta['canonical_url'], ENT_QUOTES, 'UTF-8') ?>" />
  <?php endif; ?>
  <link rel="manifest" href="/site.webmanifest" />
  <link rel="icon" type="image/x-icon" href="/favicon.ico" />
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link href="<?= asset_path('/assets/stylesheets/styles.css'); ?>" rel="stylesheet" type="text/css" />
  <link href="<?= asset_path('/assets/stylesheets/override.css'); ?>" rel="stylesheet" type="text/css" />
</head>

<body>
  <a class="skip-link" href="#container"><?= t('a11y.skip_to_content') ?></a>
  <?php require '_menu.html.php'; ?>
  <?php require '_flash.html.php'; ?>
  <main id="container" tabindex="-1">
    <?= $this->content ?>
  </main>
  <div id="footer"></div>
  <div id="footer2"></div>
</body>

</html>
