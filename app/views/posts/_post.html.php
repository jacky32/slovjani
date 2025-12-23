<li class='list-row'>
  <div></div>
  <div>
    <div><?= $post->name ?></div>
    <div class='text-xs font-semibold uppercase opacity-60'><?= $post->author->username ?></div>
  </div>
  <p class='text-xs list-col-wrap'><?= $post->body ?></p>
  <form action='/posts/destroy' method='POST'>
    <?php $this->renderCSRFToken('/posts/destroy'); ?>
    <input type='hidden' name='id' value='<?= $post->id ?>' />
    <?= ($post->author_id == $this->auth->getUserId() ? "<button class='btn btn-error' type='submit'>" . t("delete") . "</button>" : "") ?>
  </form>
</li>
