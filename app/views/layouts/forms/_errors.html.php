<div class="warning" role="alert" aria-live="assertive" aria-atomic="true">
  <p class="warning__title"><?= t('errors.validation_failed') ?></p>
  <ul class="warning__list">
    <?php foreach ($errors as $error) : ?>
      <?php $errorId = 'error-' . toSnakeCase($error['class']) . '-' . $error['attribute']; ?>
      <li id="<?= htmlspecialchars($errorId) ?>">
        <?= t("attributes." . toSnakeCase($error["class"]) . "." . $error["attribute"]) ?> <?= $error["message"] ?>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
