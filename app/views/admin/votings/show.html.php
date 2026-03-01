<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, "id" => $voting->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= $voting->name ?>
  </h1>
  <small>
    <?= t("creator") ?>: <?= $voting->creator->username ?><br>
    <?php
    if ($voting->created_at) {
      $date = new DateTime($voting->created_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("created_at") . ": " . $formatted;
    }
    ?><br>
    <?php
    if ($voting->updated_at) {
      $date = new DateTime($voting->updated_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("updated_at") . ": " . $formatted;
    }
    ?>
  </small>
  <p><?= htmlspecialchars($voting->description ?? '') ?></p>

  <hr>
  <h2><?= t("votings.show.questions_title") ?></h2>
  <ol>
    <?php foreach ($voting->questions->get() as $question) {
      $this->renderPartial("admin/votings/_question", ['voting' => $voting, 'question' => $question]);
    } ?>
  </ol>
  <?php if ($voting->status == "DRAFT") : ?>
    <a href='/admin/votings/<?= $voting->id ?>/questions/new' class='button'><?= $this->renderIcon('plus-circle') ?> <?= t("questions.new.title") ?></a>
  <?php endif; ?>
  <hr>

  <?php if ($voting->status == "DRAFT") : ?>
    <form action='/admin/votings/<?= $voting->id ?>' method='POST'>
      <?php $this->renderCSRFToken('/admin/votings/' . $voting->id); ?>
      <input type='hidden' name='voting[status]' value='IN_PROGRESS' />
      <button class='button' type='submit'><?= $this->renderIcon('play') ?> <?= t("votings.show.start_voting") ?></button>
    </form>
  <?php endif; ?>

  <?php if ($voting->status == "IN_PROGRESS") : ?>
    <?php if (!$has_voted) : ?>
      <a href='/admin/votings/<?= $voting->id ?>/users_questions/new' class='button'><?= $this->renderIcon('check') ?> <?= t("users_questions.new.title") ?></a>
    <?php else : ?>
      <p><?= t("votings.show.already_voted") ?></p>
    <?php endif; ?>
  <?php endif; ?>

  <!-- TODO: only admin -->
  <?php $this->renderPartial("admin/votings/_results", ['voting' => $voting]); ?>


  <!-- TODO: only admin -->
  <?php if ($voting->status == "IN_PROGRESS") : ?>
    <div class="action-buttons">
      <form action='/admin/votings/<?= $voting->id ?>' method='POST'>
        <?php $this->renderCSRFToken('/admin/votings/' . $voting->id); ?>
        <input type='hidden' name='voting[status]' value='COMPLETED' />
        <button class='button' type='submit'><?= $this->renderIcon('check-circle') ?> <?= t("votings.show.end_voting") ?></button>
      </form>
      <form action='/admin/votings/<?= $voting->id ?>' method='POST'>
        <?php $this->renderCSRFToken('/admin/votings/' . $voting->id); ?>
        <input type='hidden' name='voting[status]' value='CANCELLED' />
        <button class='button button--danger' type='submit'><?= $this->renderIcon('x-mark') ?> <?= t("votings.show.cancel_voting") ?></button>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($voting->status == "DRAFT" && $this->auth->hasRole(\Delight\Auth\Role::ADMIN)) : ?>
    <div class="action-buttons">
      <a href='/admin/votings/<?= $voting->id ?>/edit' class='button'><?= $this->renderIcon('pencil-square') ?> <?= t("edit") ?></a>
      <form action='/admin/votings/<?= $voting->id ?>/destroy' method='POST'>
        <?php $this->renderCSRFToken('/admin/votings/destroy'); ?>
        <input type='hidden' name='id' value='<?= $voting->id ?>' />
        <?= ($this->auth->hasRole(\Delight\Auth\Role::ADMIN) ? "<button class='button button--danger' type='submit'>" . $this->renderIcon('trash') . " " . t("delete") . "</button>" : "") ?>
      </form>
    </div>
  <?php endif; ?>


  <h3><?= t("attachments.index.title") ?></h3>
  <?php foreach ($voting->attachments->get() as $attachment): ?>
    <div class="attachment-row">
      <a href='/admin/votings/<?= $voting->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $this->renderIcon('paper-clip') ?> <?= $attachment->visible_name ?></a>
      <?php if ($voting->status == "DRAFT" && $this->auth->hasRole(\Delight\Auth\Role::ADMIN)) : ?>
        <form action='/admin/votings/<?= $voting->id ?>/attachments/<?= $attachment->id ?>/destroy' method='POST'>
          <?= $this->renderCSRFToken("/admin/votings/{$voting->id}/attachments/{$attachment->id}/destroy") ?>
          <button class='button button--danger' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <br>
  <?php if ($voting->status == "DRAFT" && $this->auth->hasRole(\Delight\Auth\Role::ADMIN)) : ?>
    <a href='/admin/votings/<?= $voting->id ?>/attachments/new' class='button'><?= $this->renderIcon('paper-clip') ?> <?= t("attachments.new.title") ?></a><br>
  <?php endif; ?>

  <?= $this->renderPartial("admin/comments/_index", [
    'comments_collection' => $voting->comments,
    'resource_type'       => 'votings',
    'resource_id'         => $voting->id,
  ]) ?>

</section>
