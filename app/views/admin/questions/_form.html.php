<form action=<?= "/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "") ?> method="POST">
  <?= $this->renderCSRFToken("/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "")) ?>

  <fieldset class="">
    <legend class=""><?= t("questions.new.title") ?></legend>
    <?= $this->renderErrors() ?>

    <?= $this->renderInput($question, "name") ?>
    <?= $this->renderTextarea($question, "description") ?>

    <button class="button"><?= $question->id ? t("update") : t("create") ?></button>
    <a href='/admin/votings/<?= $voting->id ?>' class='button'><?= t("cancel") ?></a>
  </fieldset>
</form>
