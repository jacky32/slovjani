<form action=<?= $voting->id ? "/admin/votings/" . $voting->id : "/admin/votings" ?> method="POST">
  <?= $this->renderCSRFToken($voting->id ? "/admin/votings/" . $voting->id : "/admin/votings") ?>

  <fieldset class="">
    <legend class=""><?= $voting->id ? t("votings.edit.title") : t("votings.new.title") ?></legend>
    <?= $this->renderErrors() ?>

    <?= $this->renderInput($voting, "name") ?>
    <?= $this->renderTextarea($voting, "description") ?>

    <?= $this->renderInput($voting, "datetime_start", "datetime-local") ?>
    <?= $this->renderInput($voting, "datetime_end", "datetime-local") ?>
    <br>

    <button class="button"><?= $voting->id ? t("update") : t("create") ?></button>
    <a href='/admin/votings/' class='button'><?= t("cancel") ?></a>
  </fieldset>
  <br>
</form>

<?= $this->renderPartial("layouts/_flatpickr") ?>
