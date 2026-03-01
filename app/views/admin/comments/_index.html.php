<hr>
<h3><?= t("comments.index.title") ?></h3>
<?php
$all_comments = $comments_collection->get();
$root_comments = $all_comments->filter(fn($c) => !$c->parent_comment_id);
?>
<?php foreach ($root_comments as $comment): ?>
  <?= $this->renderPartial("admin/comments/_comment", [
    'comment'       => $comment,
    'all_comments'  => $all_comments,
    'resource_type' => $resource_type,
    'resource_id'   => $resource_id,
  ]) ?>
<?php endforeach; ?>
<?php if ($all_comments->isEmpty()): ?>
  <p><?= t("comments.index.no_comments") ?></p>
<?php endif; ?>
<br>
<details class="comment-form-toggle">
  <summary class="button"><?= $this->renderIcon('plus-circle') ?> <?= t("comments.new.title") ?></summary>
  <form action="/admin/<?= $resource_type ?>/<?= $resource_id ?>/comments" method="POST" class="comment-inline-form">
    <?= $this->renderCSRFToken("/admin/{$resource_type}/{$resource_id}/comments") ?>
    <textarea name="comment[body]" class="input" rows="4" required placeholder="<?= t("comments.new.title") ?>"></textarea>
    <div class="comment-inline-form__actions">
      <button class="button" type="submit"><?= $this->renderIcon('plus-circle') ?> <?= t("create") ?></button>
    </div>
  </form>
</details>
