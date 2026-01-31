<li>
  <div style="display:flex; gap: 10px; align-items: center;justify-content: start;">
    <strong><?= $question->name ?></strong>
    <a href='/admin/votings/<?= $voting->id ?>/questions/<?= $question->id ?>/edit' class="button">
      <?= t("edit") ?>
    </a>

    <?php if ($voting->creator_id == $this->auth->getUserId()): ?>
      <form action='/admin/votings/<?= $voting->id ?>/questions/<?= $question->id ?>/destroy' method='POST'>
        <?php $this->renderCSRFToken('/admin/votings/' . $voting->id . '/questions/' . $question->id . '/destroy'); ?>
        <input type='hidden' name='id' value='<?= $question->id ?>' />
        <button class='button' type='submit'><?= t("delete") ?></button>
      </form>
    <?php endif; ?>
  </div>
</li>
