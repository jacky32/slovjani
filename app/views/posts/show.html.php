<?= $this->renderPartial("posts/_left_pane", ['posts' => $posts, "id" => $id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <!-- <?php $this->renderPartial("posts/_form", isset($errors) ? ['errors' => $errors] : []) ?> -->
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
  <form action='/posts/<?= $post->id ?>/destroy' method='POST'>
    <?php $this->renderCSRFToken('/posts/destroy'); ?>
    <input type='hidden' name='id' value='<?= $post->id ?>' />
    <?= ($post->author_id == $this->auth->getUserId() ? "<button class='btn btn-error' type='submit'>" . t("delete") . "</button>" : "") ?>
  </form>
</section>
