<form action="/posts" method="POST">
  <?php $this->renderErrors(); ?>
  <?php $this->renderCSRFToken('/posts'); ?>

  <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs">
    <legend class="fieldset-legend"><?= t("posts.new.title") ?></legend>

    <label class='my-1 floating-label'>
      <span><?= Post::humanAttributeName("name") ?></span>
      <input
        required
        type='text'
        name='name'
        placeholder='<?= Post::humanAttributeName("name") ?>'
        class='input input-md' />
    </label>

    <label class='my-1 floating-label'>
      <span><?= Post::humanAttributeName("body") ?></span>
      <textarea
        required
        name='body'
        placeholder='<?= Post::humanAttributeName("body") ?>'
        class='input input-md'></textarea>
    </label>

    <button class="mt-4 btn btn-primary"><?= t("create") ?></button>
  </fieldset>
</form>
