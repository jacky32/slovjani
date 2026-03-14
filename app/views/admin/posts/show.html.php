<?= $this->renderPartial("admin/posts/_left_pane", ['posts' => $posts, "id" => $post->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= htmlspecialchars($post->name ?? '') ?>
    <span class="status-badge status-<?= strtolower($post->status) ?>"><?= t("enums.post_statuses." . $post->status) ?></span>
  </h1>

  <div class="show-section">
    <span class="record-meta">
      <?= t("creator") ?>: <strong><?= htmlspecialchars($post->creator->username) ?></strong><br>
      <?php if ($post->created_at): ?>
        <?= t("created_at") ?>: <?= (new DateTime($post->created_at))->format('d.m.Y H:i') ?><br>
      <?php endif; ?>
      <?php if ($post->updated_at): ?>
        <?= t("updated_at") ?>: <?= (new DateTime($post->updated_at))->format('d.m.Y H:i') ?>
      <?php endif; ?>
    </span>
    <?php if (!empty($parsed_body)): ?>
      <div class="show-section__description post-parsed-content"><?= $parsed_body ?></div>
    <?php endif; ?>
    <div class="action-buttons show-section__actions">
      <a href='/admin/posts/<?= $post->id ?>/edit' class='button'><?= $this->renderIcon('pencil-square') ?> <?= t("edit") ?></a>
      <?= $this->renderDestroyButton($post) ?>
    </div>
  </div>

  <div class="show-section">
    <h3 class="show-section__title"><?= t("attachments.index.title") ?></h3>
    <?php foreach ($post->attachments->get() as $attachment): ?>
      <div class="attachment-row">
        <a href='/admin/posts/<?= $post->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $this->renderIcon('paper-clip') ?> <?= htmlspecialchars($attachment->visible_name) ?></a>
        <form action='/admin/posts/<?= $post->id ?>/attachments/<?= $attachment->id ?>/destroy' method='POST'>
          <?= $this->renderCSRFToken("/admin/posts/{$post->id}/attachments/{$attachment->id}/destroy") ?>
          <button class='button button--danger' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
        </form>
      </div>
    <?php endforeach; ?>
    <div class="show-section__add-action">
      <a href='/admin/posts/<?= $post->id ?>/attachments/new' class='button'><?= $this->renderIcon('paper-clip') ?> <?= t("attachments.new.title") ?></a>
    </div>
  </div>

  <?= $this->renderPartial("admin/comments/_index", [
    'comments_collection' => $post->comments,
    'resource_type'       => 'posts',
    'resource_id'         => $post->id,
  ]) ?>

</section>
