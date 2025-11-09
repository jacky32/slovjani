<li class='list-row'>
  <div></div>
  <div>
    <div><?= $post->get_name() ?></div>
    <div class='text-xs uppercase font-semibold opacity-60'><?= $post->get_author() ?></div>
  </div>
  <p class='list-col-wrap text-xs'><?= $post->get_body() ?></p>
  <form action='/posts/destroy' method='POST'>
    <input type='hidden' name='id' value='<?= $post->get_id() ?>' />
    <?= ($post->get_author_id() == $this->auth->getUserId() ? "<button class='btn btn-error' type='submit'>Smazat</button>" : "") ?>
  </form>
</li>
