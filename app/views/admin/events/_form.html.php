<form action=<?= $event->id ? "/admin/events/" . $event->id : "/admin/events" ?> method="POST">
  <?= $this->renderCSRFToken($event->id ? "/admin/events/" . $event->id : "/admin/events") ?>

  <fieldset class="">
    <legend class=""><?= $event->id ? t("events.edit.title") : t("events.new.title") ?></legend>
    <?= $this->renderErrors() ?>

    <?= $this->renderInput($event, "name") ?>
    <?= $this->renderTextarea($event, "description") ?>

    <?= $this->renderInput($event, "datetime_start", "datetime-local") ?>
    <?= $this->renderInput($event, "datetime_end", "datetime-local", false) ?>
    <br>
    <label for="is_publicly_visible" style="display:flex; align-items:center; gap:0.5rem;">
      <input type="checkbox" name="event[is_publicly_visible]" id="is_publicly_visible" <?= $event->is_publicly_visible ? "checked" : "" ?>>
      <span><?= t("attributes.event.is_publicly_visible") ?></span>
    </label>
    <br>

    <button class="button"><?= $event->id ? t("update") : t("create") ?></button>
    <a href='/admin/events/' class='button'><?= t("cancel") ?></a>
  </fieldset>
  <br>
</form>
