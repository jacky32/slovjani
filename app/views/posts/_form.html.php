<form action=<?= isset($post) ? "/posts/" . $post->id : "/posts" ?> method="POST">
  <?php $this->renderErrors(); ?>
  <?php $this->renderCSRFToken(isset($post) ? "/posts/" . $post->id : "/posts") ?>

  <fieldset class="">
    <legend class=""><?= t("posts.new.title") ?></legend>

    <label class=''>
      <!-- <span><?= Post::humanAttributeName("name") ?></span> -->
      <input
        required
        type='text'
        name='post[name]'
        placeholder='<?= Post::humanAttributeName("name") ?>'
        value='<?= isset($post) ? htmlspecialchars($post->name) : '' ?>' />
    </label>

    <label class=''>
      <!-- <span><?= Post::humanAttributeName("body") ?></span> -->
      <textarea
        required
        name='post[body]'
        placeholder='<?= Post::humanAttributeName("body") ?>'><?= isset($post) ? htmlspecialchars($post->body) : '' ?></textarea>
    </label>

    <label for="status-select"><?= Post::humanAttributeName("status") ?></label>
    <select id="status-select" name="post[status]">
      <option value="DRAFT" <?= isset($post) && $post->status == 'DRAFT' ? 'selected' : '' ?>><?= t("posts.status.draft") ?></option>
      <option value="PUBLISHED" <?= isset($post) && $post->status == 'PUBLISHED' ? 'selected' : '' ?>><?= t("posts.status.published") ?></option>
      <option value="ARCHIVED" <?= isset($post) && $post->status == 'ARCHIVED' ? 'selected' : '' ?>><?= t("posts.status.archived") ?></option>
    </select>
    <br>

    <button class="button"><?= isset($post) ? t("update") : t("create") ?></button>
  </fieldset>
</form>
