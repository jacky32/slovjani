<form action=<?= isset($post) ? "/posts/" . $post->id : "/posts" ?> method="POST">
  <?php $this->renderErrors(); ?>
  <?php $this->renderCSRFToken(isset($post) ? "/posts/" . $post->id : "/posts") ?>

  <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs">
    <legend class="fieldset-legend"><?= t("posts.new.title") ?></legend>

    <label class='my-1 floating-label'>
      <!-- <span><?= Post::humanAttributeName("name") ?></span> -->
      <input
        required
        type='text'
        name='post[name]'
        placeholder='<?= Post::humanAttributeName("name") ?>'
        value='<?= isset($post) ? htmlspecialchars($post->name) : '' ?>'
        class='input input-md' />
    </label>

    <label class='my-1 floating-label'>
      <!-- <span><?= Post::humanAttributeName("body") ?></span> -->
      <textarea
        required
        name='post[body]'
        placeholder='<?= Post::humanAttributeName("body") ?>'
        class='input input-md'><?= isset($post) ? htmlspecialchars($post->body) : '' ?></textarea>
    </label>

    <button class="mt-4 btn btn-primary"><?= isset($post) ? t("update") : t("create") ?></button>
  </fieldset>
</form>
