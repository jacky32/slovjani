<section id="rightpane">
  <h1><?= $voting->id ? t("votings.edit.title") : t("votings.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= $voting->id ? "/admin/votings/" . $voting->id : "/admin/votings" ?>" method="POST">
      <?= $this->renderCSRFToken($voting->id ? "/admin/votings/" . $voting->id : "/admin/votings") ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($voting, "name") ?>
      <label for="voting-description-input"><?= Voting::humanAttributeName("description") ?></label>
      <?php
      $descriptionHasError = false;
      if (!empty($errors)) {
        foreach ($errors as $error) {
          if (($error['attribute'] ?? null) === 'description') {
            $descriptionHasError = true;
            break;
          }
        }
      }
      ?>
      <textarea
        id="voting-description-input"
        class="<?= $descriptionHasError ? 'warning' : '' ?>"
        placeholder="<?= t('attributes.voting.description') ?>"
        name="voting[description]"
        aria-invalid="<?= $descriptionHasError ? 'true' : 'false' ?>"
        <?= $descriptionHasError ? 'aria-describedby="error-voting-description"' : '' ?>
        data-live-preview="true"
        data-preview-target="voting-description-preview"
        data-preview-endpoint="/admin/previews/preview_markup"
        data-preview-param="input"
        data-preview-parser="editor_markup"
        data-preview-resource-type="votings"
        data-preview-resource-id="<?= (int) ($voting->id ?? 0) ?>"
        data-preview-admin-context="1"
        data-preview-delay="2000"
        data-preview-response-key="html"
        data-preview-loading-text="<?= t('previews.loading') ?>"
        data-preview-error-text="<?= t('previews.unavailable') ?>"
        required><?= htmlspecialchars($voting->description ?? '') ?></textarea>

      <label for="voting-description-preview" style="margin-top: 12px;"><?= t('previews.parsed_preview') ?></label>
      <div id="voting-description-preview" class="post-preview-panel"><?= t('previews.loading') ?></div>

      <?= $this->renderInput($voting, "datetime_start", "datetime-local") ?>
      <?= $this->renderInput($voting, "datetime_end", "datetime-local") ?>

      <div class="action-buttons show-section__actions">
        <button class="button"><?= $this->renderIcon($voting->id ? 'pencil-square' : 'plus-circle') ?> <?= $voting->id ? t("update") : t("create") ?></button>
        <a href='<?= $voting->id ? "/admin/votings/{$voting->id}" : "/admin/votings/" ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
      </div>
    </form>
  </div>
</section>

<?= $this->renderPartial("layouts/_flatpickr") ?>
<script src="<?= asset_path('/assets/javascripts/custom/post_body_preview.js') ?>"></script>
