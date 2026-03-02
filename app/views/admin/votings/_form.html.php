<section id="rightpane">
  <h1><?= $voting->id ? t("votings.edit.title") : t("votings.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= $voting->id ? "/admin/votings/" . $voting->id : "/admin/votings" ?>" method="POST">
      <?= $this->renderCSRFToken($voting->id ? "/admin/votings/" . $voting->id : "/admin/votings") ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($voting, "name") ?>
      <?= $this->renderTextarea($voting, "description") ?>

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
