<?= $this->renderPartial("admin/events/_left_pane", ['events' => $events, "id" => $event->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= htmlspecialchars($event->name ?? '') ?>
  </h1>

  <div class="show-section">
    <span class="record-meta">
      <?= t("creator") ?>: <strong><?= htmlspecialchars($event->creator->username) ?></strong><br>
      <?php if ($event->created_at): ?>
        <?= t("created_at") ?>: <?= (new DateTime($event->created_at))->format('d.m.Y H:i') ?><br>
      <?php endif; ?>
      <?php if ($event->updated_at): ?>
        <?= t("updated_at") ?>: <?= (new DateTime($event->updated_at))->format('d.m.Y H:i') ?><br>
      <?php endif; ?>
      <?php if ($event->datetime_start): ?>
        <?= t("attributes.event.datetime_start") ?>: <?= (new DateTime($event->datetime_start))->format('d.m.Y H:i') ?><br>
      <?php endif; ?>
      <?php if ($event->datetime_end): ?>
        <?= t("attributes.event.datetime_end") ?>: <?= (new DateTime($event->datetime_end))->format('d.m.Y H:i') ?><br>
      <?php endif; ?>
      <?= t("attributes.event.is_publicly_visible") ?>: <?= $event->is_publicly_visible ? t("yes") : t("no") ?>
    </span>
    <?php if ($event->description): ?>
      <p class="show-section__description"><?= htmlspecialchars($event->description) ?></p>
    <?php endif; ?>
    <?php if ($event->creator_id == $this->auth->getUserId()): ?>
      <div class="action-buttons show-section__actions">
        <a href='/admin/events/<?= $event->id ?>/edit' class='button'><?= $this->renderIcon('pencil-square') ?> <?= t("edit") ?></a>
        <form action='/admin/events/<?= $event->id ?>/destroy' method='POST'>
          <?php $this->renderCSRFToken('/admin/events/destroy'); ?>
          <input type='hidden' name='id' value='<?= $event->id ?>' />
          <button class='button button--danger' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <div class="show-section">
    <h3 class="show-section__title"><?= t("attachments.index.title") ?></h3>
    <?php foreach ($event->attachments->get() as $attachment): ?>
      <div class="attachment-row">
        <a href='/admin/events/<?= $event->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $this->renderIcon('paper-clip') ?> <?= htmlspecialchars($attachment->visible_name) ?></a>
        <form action='/admin/events/<?= $event->id ?>/attachments/<?= $attachment->id ?>/destroy' method='POST'>
          <?= $this->renderCSRFToken("/admin/events/{$event->id}/attachments/{$attachment->id}/destroy") ?>
          <button class='button button--danger' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
        </form>
      </div>
    <?php endforeach; ?>
    <div class="show-section__add-action">
      <a href='/admin/events/<?= $event->id ?>/attachments/new' class='button'><?= $this->renderIcon('paper-clip') ?> <?= t("attachments.new.title") ?></a>
    </div>
  </div>
  <div class="show-section">
    <?= $this->renderPartial("admin/comments/_index", [
      'comments_collection' => $event->comments,
      'resource_type'       => 'events',
      'resource_id'         => $event->id,
    ]) ?>

</section>
