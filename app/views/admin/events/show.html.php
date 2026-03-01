<?= $this->renderPartial("admin/events/_left_pane", ['events' => $events, "id" => $event->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= $event->name ?>
  </h1>
  <small>
    <?= t("creator") ?>: <?= $event->creator->username ?><br>
    <?php
    if ($event->created_at) {
      $date = new DateTime($event->created_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("created_at") . ": " . $formatted;
    }
    ?><br>
    <?php
    if ($event->updated_at) {
      $date = new DateTime($event->updated_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("updated_at") . ": " . $formatted;
    }
    ?><br>
    <?php
    if ($event->datetime_start) {
      $date = new DateTime($event->datetime_start);
      $formatted = $date->format('d.m.Y H:i');
      echo t("attributes.event.datetime_start") . ": " . $formatted;
    }
    ?><br>
    <?php
    if ($event->datetime_end) {
      $date = new DateTime($event->datetime_end);
      $formatted = $date->format('d.m.Y H:i');
      echo t("attributes.event.datetime_end") . ": " . $formatted;
    }
    ?><br>
    <?= t("attributes.event.is_publicly_visible") ?>: <?= $event->is_publicly_visible ? t("yes") : t("no") ?>
  </small>
  <p><?= htmlspecialchars($event->description ?? '') ?></p>

  <?php if ($event->creator_id == $this->auth->getUserId()) : ?>
    <div class="action-buttons">
      <a href='/admin/events/<?= $event->id ?>/edit' class='button'><?= t("edit") ?></a>
      <form action='/admin/events/<?= $event->id ?>/destroy' method='POST'>
        <?php $this->renderCSRFToken('/admin/events/destroy'); ?>
        <input type='hidden' name='id' value='<?= $event->id ?>' />
        <?= ($event->creator_id == $this->auth->getUserId() ? "<button class='button button--danger' type='submit'>" . t("delete") . "</button>" : "") ?>
      </form>
    </div>
  <?php endif; ?>

  <hr>
  <h3><?= t("attachments.index.title") ?></h3>
  <?php foreach ($event->attachments->get() as $attachment): ?>
    <div class="attachment-row">
      <a href='/admin/events/<?= $event->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $attachment->visible_name ?></a>
      <form action='/admin/events/<?= $event->id ?>/attachments/<?= $attachment->id ?>/destroy' method='POST'>
        <?= $this->renderCSRFToken("/admin/events/{$event->id}/attachments/{$attachment->id}/destroy") ?>
        <button class='button button--danger' type='submit'><?= t("delete") ?></button>
      </form>
    </div>
  <?php endforeach; ?>
  <br>
  <a href='/admin/events/<?= $event->id ?>/attachments/new' class='button'><?= t("attachments.new.title") ?></a>

  <?= $this->renderPartial("admin/comments/_index", [
    'comments_collection' => $event->comments,
    'resource_type'       => 'events',
    'resource_id'         => $event->id,
  ]) ?>

</section>
