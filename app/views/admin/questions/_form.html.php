<section id="rightpane">
  <h1><?= t("questions.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= "/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "") ?>" method="POST">
      <?= $this->renderCSRFToken("/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "")) ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($question, "name") ?>
      <?= $this->renderTextarea($question, "description") ?>

      <div class="action-buttons show-section__actions">
        <button class="button"><?= $this->renderIcon($question->id ? 'pencil-square' : 'plus-circle') ?> <?= $question->id ? t("update") : t("create") ?></button>
        <a href='/admin/votings/<?= $voting->id ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
      </div>
    </form>
  </div>
</section>
