<form action=<?= $voting->id ? "/admin/votings/" . $voting->id : "/admin/votings" ?> method="POST">
  <?= $this->renderErrors($errors) ?>
  <?= $this->renderCSRFToken($voting->id ? "/admin/votings/" . $voting->id : "/admin/votings") ?>

  <fieldset class="">
    <legend class=""><?= $voting->id ? t("votings.edit.title") : t("votings.new.title") ?></legend>

    <label class=''>
      <input
        required
        type='text'
        name='voting[name]'
        placeholder='<?= Voting::humanAttributeName("name") ?>'
        value='<?= htmlspecialchars($voting->name ?? '') ?>' />
    </label>


    <label class=''>
      <textarea
        required
        name='voting[description]'
        placeholder='<?= Voting::humanAttributeName("description") ?>'><?= htmlspecialchars($voting->description ?? '') ?></textarea>
    </label>


    <label class=''>
      <input
        required
        type='datetime-local'
        name='voting[datetime_start]'
        placeholder='<?= Voting::humanAttributeName("datetime_start") ?>'
        value='<?= $voting->datetime_start ?>' />
    </label>

    <label class=''>
      <input
        required
        type='datetime-local'
        name='voting[datetime_end]'
        placeholder='<?= Voting::humanAttributeName("datetime_end") ?>'
        value='<?= $voting->datetime_end ?>' />
    </label>
    <br>

    <button class="button"><?= $voting->id ? t("update") : t("create") ?></button>
    <a href='/admin/votings/' class='button'><?= t("cancel") ?></a>
  </fieldset>
  <br>
</form>
