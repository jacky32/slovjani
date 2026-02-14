<?= $this->renderPartial("posts/_left_pane", ['posts' => $posts, "id" => $post->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= $post->name ?>
  </h1>
  <small>
    <?= t("creator") ?>: <?= $post->creator->username ?><br>
    <?php
    if ($post->created_at) {
      $date = new DateTime($post->created_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("created_at") . ": " . $formatted;
    }
    ?><br>
    <?php
    if ($post->updated_at) {
      $date = new DateTime($post->updated_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("updated_at") . ": " . $formatted;
    }
    ?>
  </small>
  <p><?= htmlspecialchars($post->body ?? '') ?></p>

  <hr>
  <h3><?= t("attachments.index.title") ?></h3>
  <?php foreach ($attachments as $attachment): ?>
    <div style="display:flex; align-items:center; justify-items: center; gap:10px;">
      <a href='/posts/<?= $post->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $attachment->visible_name ?></a><br>
    </div>
  <?php endforeach; ?>
  <br>
</section>
