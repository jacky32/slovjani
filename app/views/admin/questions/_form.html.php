<section id="rightpane">
  <h1><?= t("questions.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= "/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "") ?>" method="POST">
      <?= $this->renderCSRFToken("/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "")) ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($question, "name") ?>
      <label for="question-description-input"><?= Question::humanAttributeName("description") ?></label>
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
        id="question-description-input"
        class="<?= $descriptionHasError ? 'warning' : '' ?>"
        placeholder="<?= t('attributes.question.description') ?>"
        name="question[description]"
        data-live-preview="true"
        data-preview-target="question-description-preview"
        data-preview-endpoint="/admin/previews/preview_markup"
        data-preview-param="input"
        data-preview-parser="editor_markup"
        data-preview-delay="2000"
        data-preview-response-key="html"
        data-preview-loading-text="<?= t('previews.loading') ?>"
        data-preview-error-text="<?= t('previews.unavailable') ?>"
        required><?= htmlspecialchars($question->description ?? '') ?></textarea>

      <label for="question-description-preview" style="margin-top: 12px;"><?= t('previews.parsed_preview') ?></label>
      <div id="question-description-preview" class="post-preview-panel"><?= t('previews.loading') ?></div>

      <div class="action-buttons show-section__actions">
        <button class="button"><?= $this->renderIcon($question->id ? 'pencil-square' : 'plus-circle') ?> <?= $question->id ? t("update") : t("create") ?></button>
        <a href='/admin/votings/<?= $voting->id ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
      </div>
    </form>
  </div>
</section>
<script src="<?= asset_path('/assets/javascripts/custom/post_body_preview.js') ?>"></script>
