<?= $this->renderPartial("events/_left_pane", ['events' => $events, "id" => $event->id, 'errors' => isset($errors) ? $errors : []]) ?>
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
    ?>
  </small>
  <?php if (!empty($parsed_description)): ?>
    <div class="post-parsed-content"><?= $parsed_description ?></div>
  <?php endif; ?>

  <hr>
  <h3><?= t("attachments.index.title") ?></h3>
  <?php foreach ($attachments as $attachment): ?>
    <div style="display:flex; align-items:center; justify-items: center; gap:10px;">
      <a href='/events/<?= $event->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $this->renderIcon('paper-clip') ?> <?= $attachment->visible_name ?></a><br>
    </div>
  <?php endforeach; ?>
  <br>
</section>
