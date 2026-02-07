<?= $this->renderPartial("admin/posts/_left_pane", ['posts' => $posts, "id" => $post->id, 'errors' => isset($errors) ? $errors : []]) ?>
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
    ?><br>
    <?= t("attributes.post.status") ?>: <?= t("enums.post_statuses." . $post->status) ?>
  </small>
  <p><?= htmlspecialchars($post->body ?? '') ?></p>
  <a href='/admin/posts/<?= $post->id ?>/edit' class='button'><?= t("edit") ?></a>

  <?= $this->renderDestroyButton($post) ?>
</section>
