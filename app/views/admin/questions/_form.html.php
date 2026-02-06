<form action=<?= "/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "") ?> method="POST">
  <?= $this->renderCSRFToken("/admin/votings/" . $voting->id . "/questions" . ($question->id ? "/" . $question->id : "")) ?>

  <fieldset class="">
    <legend class=""><?= t("questions.new.title") ?></legend>
    <?= $this->renderErrors($errors) ?>

    <label class=''>
      <input
        required
        type='text'
        class='<?= $this->isAttributeInvalid($errors, "name") ?>'
        name='question[name]'
        placeholder='<?= Question::humanAttributeName("name") ?>'
        value='<?= htmlspecialchars($question->name ?? '') ?>' />
    </label>


    <label class=''>
      <textarea
        required
        name='question[description]'
        class='<?= $this->isAttributeInvalid($errors, "description") ?>'
        placeholder='<?= Question::humanAttributeName("description") ?>'><?= htmlspecialchars($question->description ?? '') ?></textarea>
    </label>

    <button class="button"><?= $question->id ? t("update") : t("create") ?></button>
    <a href='/admin/votings/<?= $voting->id ?>' class='button'><?= t("cancel") ?></a>
  </fieldset>
</form>
