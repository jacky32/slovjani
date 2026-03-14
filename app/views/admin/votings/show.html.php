<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, "id" => $voting->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= htmlspecialchars($voting->name) ?>
    <span class="status-badge status-<?= strtolower($voting->status) ?>"><?= t("enums.voting_statuses." . $voting->status) ?></span>
  </h1>

  <div class="show-section">
    <small class="record-meta">
      <?= t("creator") ?>: <strong><?= htmlspecialchars($voting->creator->username) ?></strong><br>
      <?php if ($voting->created_at): ?>
        <?= t("created_at") ?>: <?= (new DateTime($voting->created_at))->format('d.m.Y H:i') ?><br>
      <?php endif; ?>
      <?php if ($voting->updated_at): ?>
        <?= t("updated_at") ?>: <?= (new DateTime($voting->updated_at))->format('d.m.Y H:i') ?>
      <?php endif; ?>
    </small>
    <?php if (!empty($parsed_description)): ?>
      <div class="show-section__description post-parsed-content"><?= $parsed_description ?></div>
    <?php endif; ?>
    <?php if ($voting->status == "DRAFT" && $this->auth->hasRole(\Delight\Auth\Role::ADMIN)): ?>
      <div class="action-buttons show-section__actions">
        <a href='/admin/votings/<?= $voting->id ?>/edit' class='button'><?= $this->renderIcon('pencil-square') ?> <?= t("edit") ?></a>
        <form action='/admin/votings/<?= $voting->id ?>/destroy' method='POST'>
          <?php $this->renderCSRFToken('/admin/votings/destroy'); ?>
          <input type='hidden' name='id' value='<?= $voting->id ?>' />
          <button class='button button--danger' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <div class="show-section">
    <h2 class="show-section__title"><?= t("votings.show.questions_title") ?></h2>
    <ol>
      <?php foreach ($voting->questions->get() as $question): ?>
        <?php $this->renderPartial("admin/votings/_question", ['voting' => $voting, 'question' => $question]); ?>
      <?php endforeach; ?>
    </ol>
    <?php if ($voting->status == "DRAFT"): ?>
      <a href='/admin/votings/<?= $voting->id ?>/questions/new' class='button'><?= $this->renderIcon('plus-circle') ?> <?= t("questions.new.title") ?></a>
    <?php endif; ?>
  </div>

  <?php if ($voting->status == "DRAFT"): ?>
    <div class="show-section">
      <h2 class="show-section__title"><?= t("votings.show.start_voting") ?></h2>
      <form action='/admin/votings/<?= $voting->id ?>' method='POST'>
        <?php $this->renderCSRFToken('/admin/votings/' . $voting->id); ?>
        <input type='hidden' name='voting[status]' value='IN_PROGRESS' />
        <button class='button' type='submit'><?= $this->renderIcon('play') ?> <?= t("votings.show.start_voting") ?></button>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($voting->status == "IN_PROGRESS"): ?>
    <div class="show-section">
      <?php if (!$has_voted): ?>
        <a href='/admin/votings/<?= $voting->id ?>/users_questions/new' class='button'><?= $this->renderIcon('check') ?> <?= t("users_questions.new.title") ?></a>
      <?php else: ?>
        <p><?= t("votings.show.already_voted") ?></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php $this->renderPartial("admin/votings/_results", ['voting' => $voting]); ?>
  <?php if ($voting->status == "IN_PROGRESS"): ?>
    <div class="show-section">
      <h2 class="show-section__title"><?= t("votings.show.end_voting") ?></h2>
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
    </div>
  <?php endif; ?>

  <div class="show-section">
    <h3 class="show-section__title"><?= t("attachments.index.title") ?></h3>
    <?php foreach ($voting->attachments->get() as $attachment): ?>
      <div class="attachment-row">
        <a href='/admin/votings/<?= $voting->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $this->renderIcon('paper-clip') ?> <?= htmlspecialchars($attachment->visible_name) ?></a>
        <?php if ($voting->status == "DRAFT" && $this->auth->hasRole(\Delight\Auth\Role::ADMIN)): ?>
          <form action='/admin/votings/<?= $voting->id ?>/attachments/<?= $attachment->id ?>/destroy' method='POST'>
            <?= $this->renderCSRFToken("/admin/votings/{$voting->id}/attachments/{$attachment->id}/destroy") ?>
            <button class='button button--danger' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if ($voting->status == "DRAFT" && $this->auth->hasRole(\Delight\Auth\Role::ADMIN)): ?>
      <div class="show-section__add-action">
        <a href='/admin/votings/<?= $voting->id ?>/attachments/new' class='button'><?= $this->renderIcon('paper-clip') ?> <?= t("attachments.new.title") ?></a>
      </div>
    <?php endif; ?>
  </div>

  <?= $this->renderPartial("admin/comments/_index", [
    'comments_collection' => $voting->comments,
    'resource_type'       => 'votings',
    'resource_id'         => $voting->id,
  ]) ?>

</section>
