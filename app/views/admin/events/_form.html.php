<section id="rightpane">
  <h1><?= $event->id ? t("events.edit.title") : t("events.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= $event->id ? "/admin/events/" . $event->id : "/admin/events" ?>" method="POST">
      <?= $this->renderCSRFToken($event->id ? "/admin/events/" . $event->id : "/admin/events") ?>

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

      <div class="action-buttons show-section__actions">
        <button class="button"><?= $this->renderIcon($event->id ? 'pencil-square' : 'plus-circle') ?> <?= $event->id ? t("update") : t("create") ?></button>
        <a href='<?= $event->id ? "/admin/events/{$event->id}" : "/admin/events/" ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
      </div>
    </form>
  </div>
</section>

<?= $this->renderPartial("layouts/_flatpickr") ?>
