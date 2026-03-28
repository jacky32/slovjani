<li>
  <div style="display:flex; gap: 10px; align-items: center;justify-content: start;">
    <strong><?= $question->name ?></strong>
    <?php if ($voting->status == "DRAFT"): ?>
      <a href='/admin/votings/<?= $voting->id ?>/questions/<?= $question->id ?>/edit' class="button">
        <?= $this->renderIcon('pencil-square') ?> <?= t("edit") ?>
      </a>
    <?php endif; ?>

    <?php if ($voting->status == "DRAFT" && $this->auth->hasRole(\App\Models\User::AVAILABLE_ROLES['admin'])): ?>
      <form action='/admin/votings/<?= $voting->id ?>/questions/<?= $question->id ?>/destroy' method='POST'>
        <?php $this->renderCSRFToken('/admin/votings/' . $voting->id . '/questions/' . $question->id . '/destroy'); ?>
        <input type='hidden' name='id' value='<?= $question->id ?>' />
        <button class='button' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
      </form>
    <?php endif; ?>
  </div>
</li>
