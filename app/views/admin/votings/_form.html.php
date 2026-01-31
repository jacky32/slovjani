<form action=<?= isset($voting) ? "/admin/votings/" . $voting->id : "/admin/votings" ?> method="POST">
  <?php $this->renderErrors(); ?>
  <?php $this->renderCSRFToken(isset($voting) ? "/admin/votings/" . $voting->id : "/admin/votings") ?>

  <fieldset class="">
    <legend class=""><?= isset($voting) ? t("votings.edit.title") : t("votings.new.title") ?></legend>

    <label class=''>
      <input
        required
        type='text'
        name='voting[name]'
        placeholder='<?= Voting::humanAttributeName("name") ?>'
        value='<?= isset($voting) ? htmlspecialchars($voting->name) : '' ?>' />
    </label>


    <label class=''>
      <textarea
        required
        name='voting[description]'
        placeholder='<?= Voting::humanAttributeName("description") ?>'><?= isset($voting) ? htmlspecialchars($voting->description) : '' ?></textarea>
    </label>


    <label class=''>
      <input
        required
        type='datetime-local'
        name='voting[datetime_start]'
        placeholder='<?= Voting::humanAttributeName("datetime_start") ?>'
        value='<?= isset($voting) ? htmlspecialchars($voting->datetime_start) : '' ?>' />
    </label>

    <label class=''>
      <input
        required
        type='datetime-local'
        name='voting[datetime_end]'
        placeholder='<?= Voting::humanAttributeName("datetime_end") ?>'
        value='<?= isset($voting) ? htmlspecialchars($voting->datetime_end) : '' ?>' />
    </label>
    <br>

    <button class="button"><?= isset($voting) ? t("update") : t("create") ?></button>
    <a href='/admin/votings/' class='button'><?= t("cancel") ?></a>
  </fieldset>
  <br>
</form>
