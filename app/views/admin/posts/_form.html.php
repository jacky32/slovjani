<section id="rightpane">
  <h1><?= t("posts.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= $post->id ? "/admin/posts/" . $post->id : "/admin/posts" ?>" method="POST">
      <?= $this->renderCSRFToken($post->id ? "/admin/posts/" . $post->id : "/admin/posts") ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($post, "name") ?>
      <?= $this->renderTextArea($post, "body") ?>

      <label for="status-select"><?= Post::humanAttributeName("status") ?></label>
      <select id="status-select" name="post[status]">
        <option value="DRAFT" <?= $post->status == 'DRAFT' ? 'selected' : '' ?>><?= t("enums.post_statuses.DRAFT") ?></option>
        <option value="PUBLISHED" <?= $post->status == 'PUBLISHED' ? 'selected' : '' ?>><?= t("enums.post_statuses.PUBLISHED") ?></option>
        <option value="ARCHIVED" <?= $post->status == 'ARCHIVED' ? 'selected' : '' ?>><?= t("enums.post_statuses.ARCHIVED") ?></option>
      </select>

      <div class="action-buttons show-section__actions">
        <button class="button"><?= $this->renderIcon($post->id ? 'pencil-square' : 'plus-circle') ?> <?= $post->id ? t("update") : t("create") ?></button>
        <a href='<?= $post->id ? "/admin/posts/{$post->id}" : "/admin/posts/" ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
      </div>
    </form>
  </div>
</section>
