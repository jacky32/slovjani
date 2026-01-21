<?= $this->renderPartial("posts/_left_pane", ['posts' => $posts, "id" => $post->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= $post->name ?>
  </h1>
  <small>
    <?= t("posts.show.author") ?>: <?= $post->author->username ?><br>
    <?php
    if (isset($post->created_at)) {
      $date = new DateTime($post->created_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("posts.show.created_at") . ": " . $formatted;
    }
    ?>
  </small>
  <p><?= $post->body ?></p>
  <a href='/posts/<?= $post->id ?>/edit' class='button'><?= t("edit") ?></a>
  <form action='/posts/<?= $post->id ?>/destroy' method='POST'>
    <?php $this->renderCSRFToken('/posts/destroy'); ?>
    <input type='hidden' name='id' value='<?= $post->id ?>' />
    <?= ($post->author_id == $this->auth->getUserId() ? "<button class='btn btn-error' type='submit'>" . t("delete") . "</button>" : "") ?>
  </form>
</section>
