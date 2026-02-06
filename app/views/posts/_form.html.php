<form action=<?= $post->id ? "/posts/" . $post->id : "/posts" ?> method="POST">
  <?= $this->renderCSRFToken($post->id ? "/posts/" . $post->id : "/posts") ?>

  <fieldset class="">
    <legend class=""><?= t("posts.new.title") ?></legend>

    <?= $this->renderErrors() ?>

    <?= $this->renderInput($post, "name") ?>
    <?= $this->renderTextArea($post, "body") ?>

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
