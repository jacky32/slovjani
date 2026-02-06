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
  <a href='/posts/<?= $post->id ?>/edit' class='button'><?= t("edit") ?></a>
  <form action='/posts/<?= $post->id ?>/destroy' method='POST'>
    <?php $this->renderCSRFToken('/posts/destroy'); ?>
    <input type='hidden' name='id' value='<?= $post->id ?>' />
    <?= ($post->creator_id == $this->auth->getUserId() ? "<button class='button' type='submit'>" . t("delete") . "</button>" : "") ?>
  </form>
</section>
