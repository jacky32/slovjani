<form action=<?= "/admin/{$resource_type}/{$resource_id}/attachments" . ($attachment->id ? "/" . $attachment->id : "") ?> method="POST" enctype="multipart/form-data">
  <?= $this->renderCSRFToken("/admin/{$resource_type}/{$resource_id}/attachments" . ($attachment->id ? "/" . $attachment->id : "")) ?>

  <fieldset class="">
    <legend class=""><?= t("attachments.new.title") ?></legend>
    <?= $this->renderErrors() ?>

    <input type="file" name="attachment[]" class="input" <?= $attachment->id ? "" : "required" ?>>

    <button class="button"><?= $attachment->id ? t("update") : t("create") ?></button>
    <a href='/admin/<?= $resource_type ?>/<?= $resource_id ?>' class='button'><?= t("cancel") ?></a>
  </fieldset>
</form>
