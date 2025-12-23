<li class='list-row'>
  <div></div>
  <div>
    <div><?= $post->name ?></div>
    <div class='text-xs uppercase font-semibold opacity-60'><?= $post->get_author() ?></div>
  </div>
  <p class='list-col-wrap text-xs'><?= $post->body ?></p>
  <form action='/posts/destroy' method='POST'>
    <?php $this->renderCSRFToken('/posts/destroy'); ?>
    <input type='hidden' name='id' value='<?= $post->id ?>' />
    <?= ($post->author_id == $this->auth->getUserId() ? "<button class='btn btn-error' type='submit'>Smazat</button>" : "") ?>
  </form>
</li>
