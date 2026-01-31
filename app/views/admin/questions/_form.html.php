<form action=<?= "/admin/votings/" . $voting->id . "/questions" . (isset($question) ? "/" . $question->id : "") ?> method="POST">
  <?php $this->renderErrors(); ?>
  <?php $this->renderCSRFToken("/admin/votings/" . $voting->id . "/questions" . (isset($question) ? "/" . $question->id : "")) ?>

  <fieldset class="">
    <legend class=""><?= t("questions.new.title") ?></legend>

    <label class=''>
      <input
        required
        type='text'
        name='question[name]'
        placeholder='<?= Question::humanAttributeName("name") ?>'
        value='<?= isset($question) ? htmlspecialchars($question->name) : '' ?>' />
    </label>


    <label class=''>
      <textarea
        required
        name='question[description]'
        placeholder='<?= Question::humanAttributeName("description") ?>'><?= isset($question) ? htmlspecialchars($question->description) : '' ?></textarea>
    </label>

    <button class="button"><?= isset($question) ? t("update") : t("create") ?></button>
    <a href='/admin/votings/<?= $voting->id ?>' class='button'><?= t("cancel") ?></a>
  </fieldset>
</form>
