<form action=<?= "/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "") ?> method="POST">
  <?= $this->renderCSRFToken("/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "")) ?>

  <fieldset class="">
    <legend class=""><?= t("questions.new.title") ?></legend>
    <?= $this->renderErrors() ?>

    <?= $this->renderInput($question, "name") ?>
    <?= $this->renderTextarea($question, "description") ?>

    <button class="button"><?= $this->renderIcon($question->id ? 'pencil-square' : 'plus-circle') ?> <?= $question->id ? t("update") : t("create") ?></button>
    <a href='/admin/votings/<?= $voting->id ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
  </fieldset>
</form>
