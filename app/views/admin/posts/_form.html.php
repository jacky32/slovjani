<section id="rightpane">
  <h1><?= t("posts.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= $post->id ? "/admin/posts/" . $post->id : "/admin/posts" ?>" method="POST">
      <?= $this->renderCSRFToken($post->id ? "/admin/posts/" . $post->id : "/admin/posts") ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($post, "name") ?>

      <label for="post-body-input"><?= Post::humanAttributeName("body") ?></label>
      <?php
      $bodyHasError = false;
      if (!empty($errors)) {
        foreach ($errors as $error) {
          if (($error['attribute'] ?? null) === 'body') {
            $bodyHasError = true;
            break;
          }
        }
      }
      ?>
      <textarea
        id="post-body-input"
        class="<?= $bodyHasError ? 'warning' : '' ?>"
        placeholder="<?= t('attributes.post.body') ?>"
        name="post[body]"
        data-live-preview="true"
        data-preview-target="post-body-preview"
        data-preview-endpoint="/admin/previews/preview_markup"
        data-preview-param="input"
        data-preview-parser="editor_markup"
        data-preview-resource-type="posts"
        data-preview-resource-id="<?= (int) ($post->id ?? 0) ?>"
        data-preview-admin-context="1"
        data-preview-delay="2000"
        data-preview-response-key="html"
        data-preview-loading-text="<?= t('previews.loading') ?>"
        data-preview-error-text="<?= t('previews.unavailable') ?>"
        required><?= htmlspecialchars($post->body ?? '') ?></textarea>

      <label for="post-body-preview" style="margin-top: 12px;"><?= t('previews.parsed_preview') ?></label>
      <div id="post-body-preview" class="post-preview-panel"><?= t('previews.loading') ?></div>

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
<script src="<?= asset_path('/assets/javascripts/custom/post_body_preview.js') ?>"></script>
