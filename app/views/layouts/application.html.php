<!doctype html>
<html>

<head>
  <meta charset="UTF-8" />
  <title><?= $this->title ?></title>
  <link href="<?= asset_path('/assets/stylesheets/styles.css'); ?>" rel="stylesheet" type="text/css" />
  <link href="<?= asset_path('/assets/stylesheets/override.css'); ?>" rel="stylesheet" type="text/css" />
</head>

<body>
  <?php require '_menu.html.php'; ?>
  <?php require '_flash.html.php'; ?>
  <main id="container">
    <?= $this->content ?>
  </main>
  <div id="footer"></div>
  <div id="footer2"></div>
</body>

</html>
