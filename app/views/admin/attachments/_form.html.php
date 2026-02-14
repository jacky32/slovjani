<form action=<?= "/admin/{$resource_type}/{$resource_id}/attachments" . ($attachment->id ? "/" . $attachment->id : "") ?> method="POST" enctype="multipart/form-data">
  <?= $this->renderCSRFToken("/admin/{$resource_type}/{$resource_id}/attachments" . ($attachment->id ? "/" . $attachment->id : "")) ?>

  <fieldset class="">
    <legend class=""><?= t("attachments.new.title") ?></legend>
    <?= $this->renderErrors() ?>

    <?= $this->renderInput($attachment, "visible_name", t("attachments.form.visible_name")) ?>
    <br>
    <label for="is_publicly_visible" style="display:flex; align-items:center; gap:0.5rem;">
      <input type="checkbox" name="attachment[is_publicly_visible]" id="is_publicly_visible" <?= $attachment->is_publicly_visible ? "checked" : "" ?>>
      <span><?= t("attributes.attachment.is_publicly_visible") ?></span>
    </label>
    <br>

    <input type="file" name="attachment[]" class="input" <?= $attachment->id ? "" : "required" ?>>

    <br>
    <button class="button"><?= $attachment->id ? t("update") : t("create") ?></button>
    <a href='/admin/<?= $resource_type ?>/<?= $resource_id ?>' class='button'><?= t("cancel") ?></a>
  </fieldset>
</form>
