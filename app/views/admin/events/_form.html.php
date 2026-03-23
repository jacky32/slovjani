<section id="rightpane">
  <h1><?= $event->id ? t("events.edit.title") : t("events.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= $event->id ? "/admin/events/" . $event->id : "/admin/events" ?>" method="POST">
      <?= $this->renderCSRFToken($event->id ? "/admin/events/" . $event->id : "/admin/events") ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($event, "name") ?>
      <label for="event-description-input"><?= Event::humanAttributeName("description") ?></label>
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
        id="event-description-input"
        class="<?= $descriptionHasError ? 'warning' : '' ?>"
        placeholder="<?= t('attributes.event.description') ?>"
        name="event[description]"
        aria-invalid="<?= $descriptionHasError ? 'true' : 'false' ?>"
        <?= $descriptionHasError ? 'aria-describedby="error-event-description"' : '' ?>
        data-live-preview="true"
        data-preview-target="event-description-preview"
        data-preview-endpoint="/admin/previews/preview_markup"
        data-preview-param="input"
        data-preview-parser="editor_markup"
        data-preview-resource-type="events"
        data-preview-resource-id="<?= (int) ($event->id ?? 0) ?>"
        data-preview-admin-context="1"
        data-preview-delay="2000"
        data-preview-response-key="html"
        data-preview-loading-text="<?= t('previews.loading') ?>"
        data-preview-error-text="<?= t('previews.unavailable') ?>"
        required><?= htmlspecialchars($event->description ?? '') ?></textarea>

      <label for="event-description-preview" style="margin-top: 12px;"><?= t('previews.parsed_preview') ?></label>
      <div id="event-description-preview" class="post-preview-panel"><?= t('previews.loading') ?></div>

      <?= $this->renderInput($event, "datetime_start", "datetime-local") ?>
      <?= $this->renderInput($event, "datetime_end", "datetime-local", false) ?>
      <br>
      <label for="is_publicly_visible" style="display:flex; align-items:center; gap:0.5rem;">
        <input type="checkbox" name="event[is_publicly_visible]" id="is_publicly_visible" <?= $event->is_publicly_visible ? "checked" : "" ?>>
        <span><?= t("attributes.event.is_publicly_visible") ?></span>
      </label>

      <div class="action-buttons show-section__actions">
        <button class="button"><?= $this->renderIcon($event->id ? 'pencil-square' : 'plus-circle') ?> <?= $event->id ? t("update") : t("create") ?></button>
        <a href='<?= $event->id ? "/admin/events/{$event->id}" : "/admin/events/" ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
      </div>
    </form>
  </div>
</section>

<?= $this->renderPartial("layouts/_flatpickr") ?>
<script src="<?= asset_path('/assets/javascripts/custom/post_body_preview.js') ?>"></script>
