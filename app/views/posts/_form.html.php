<form action=<?= $post->id ? "/posts/" . $post->id : "/posts" ?> method="POST">
  <?= $this->renderCSRFToken($post->id ? "/posts/" . $post->id : "/posts") ?>

  <fieldset class="">
    <legend class=""><?= t("posts.new.title") ?></legend>

    <?= $this->renderErrors($errors) ?>
    <label class=''>
      <!-- <span><?= Post::humanAttributeName("name") ?></span> -->
      <input
        required
        type='text'
        name='post[name]'
        placeholder='<?= Post::humanAttributeName("name") ?>'
        value='<?= htmlspecialchars($post->name ?? '') ?>' />
    </label>

    <label class=''>
      <!-- <span><?= Post::humanAttributeName("body") ?></span> -->
      <textarea
        required
        name='post[body]'
        placeholder='<?= Post::humanAttributeName("body") ?>'><?= htmlspecialchars($post->body ?? '') ?></textarea>
    </label>

    <label for="status-select"><?= Post::humanAttributeName("status") ?></label>
    <select id="status-select" name="post[status]">
      <option value="DRAFT" <?= $post->status == 'DRAFT' ? 'selected' : '' ?>><?= t("posts.status.draft") ?></option>
      <option value="PUBLISHED" <?= $post->status == 'PUBLISHED' ? 'selected' : '' ?>><?= t("posts.status.published") ?></option>
      <option value="ARCHIVED" <?= $post->status == 'ARCHIVED' ? 'selected' : '' ?>><?= t("posts.status.archived") ?></option>
    </select>
    <br>

    <button class="button"><?= $post->id ? t("update") : t("create") ?></button>
  </fieldset>
</form>
