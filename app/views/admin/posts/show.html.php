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
  <br>

  <p><?= htmlspecialchars($post->body ?? '') ?></p>

  <div class="action-buttons">
    <a href='/admin/posts/<?= $post->id ?>/edit' class='button'><?= t("edit") ?></a>
    <?= $this->renderDestroyButton($post) ?>
  </div>

  <hr>
  <h3><?= t("attachments.index.title") ?></h3>
  <?php foreach ($post->attachments->get() as $attachment): ?>
    <div class="attachment-row">
      <a href='/admin/posts/<?= $post->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $attachment->visible_name ?></a>
      <form action='/admin/posts/<?= $post->id ?>/attachments/<?= $attachment->id ?>/destroy' method='POST'>
        <?= $this->renderCSRFToken("/admin/posts/{$post->id}/attachments/{$attachment->id}/destroy") ?>
        <button class='button button--danger' type='submit'><?= t("delete") ?></button>
      </form>
    </div>
  <?php endforeach; ?>
  <br>
  <a href='/admin/posts/<?= $post->id ?>/attachments/new' class='button'><?= t("attachments.new.title") ?></a>

  <?= $this->renderPartial("admin/comments/_index", [
    'comments_collection' => $post->comments,
    'resource_type'       => 'posts',
    'resource_id'         => $post->id,
  ]) ?>

</section>
