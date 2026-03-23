<section id="rightpane">
  <h1><?= t("attachments.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= "/admin/{$resource_type}/{$resource_id}/attachments" . ($attachment->id ? "/" . $attachment->id : "") ?>" method="POST" enctype="multipart/form-data">
      <?= $this->renderCSRFToken("/admin/{$resource_type}/{$resource_id}/attachments" . ($attachment->id ? "/" . $attachment->id : "")) ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($attachment, "visible_name") ?>
      <label for="is_publicly_visible" style="display:flex; align-items:center; gap:0.5rem;">
        <input type="checkbox" name="attachment[is_publicly_visible]" id="is_publicly_visible" <?= $attachment->is_publicly_visible ? "checked" : "" ?>>
        <span><?= t("attributes.attachment.is_publicly_visible") ?></span>
      </label>

      <label for="attachment-file-input"><?= t('a11y.attachment_file') ?></label>
      <input type="file" id="attachment-file-input" name="attachment[]" class="input" <?= $attachment->id ? "" : "required" ?> aria-describedby="attachment-file-help">
      <small id="attachment-file-help" class="form-help"><?= t('a11y.attachment_file_help') ?></small>

      <div class="action-buttons show-section__actions">
        <button class="button"><?= $this->renderIcon($attachment->id ? 'pencil-square' : 'plus-circle') ?> <?= $attachment->id ? t("update") : t("create") ?></button>
        <a href='/admin/<?= $resource_type ?>/<?= $resource_id ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
      </div>
    </form>
  </div>
</section>
