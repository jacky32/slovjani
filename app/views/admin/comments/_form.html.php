<?php
$inline  = $inline  ?? false;
$destroy = $destroy ?? false;
$base    = "/admin/{$resource_type}/{$resource_id}/comments";
$action  = $destroy
  ? "{$base}/{$comment->id}/destroy"
  : $base . ($comment->id ? "/{$comment->id}" : "");
?>

<?php if ($destroy): ?>

  <form action="<?= $action ?>" method="POST">
    <?= $this->renderCSRFToken($action) ?>
    <button class="button" type="submit"><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
  </form>

<?php elseif ($inline): ?>

  <form action="<?= $action ?>" method="POST" class="comment-inline-form">
    <?= $this->renderCSRFToken($action) ?>
    <?php if (!$comment->id && isset($parent_comment_id) && $parent_comment_id): ?>
      <input type="hidden" name="comment[parent_comment_id]" value="<?= htmlspecialchars($parent_comment_id) ?>">
    <?php endif; ?>
    <textarea name="comment[body]" class="input" rows="3" required
      <?= !$comment->id ? 'placeholder="' . t("comments.new.title") . '"' : '' ?>><?= $comment->id ? htmlspecialchars($comment->body ?? '') : '' ?></textarea>
    <div class="comment-inline-form__actions">
      <button class="button" type="submit"><?= $this->renderIcon($comment->id ? 'pencil-square' : 'plus-circle') ?> <?= $comment->id ? t("update") : t("create") ?></button>
      <button class="button" type="button" onclick="this.closest('details').removeAttribute('open')"><?= $this->renderIcon('x-mark') ?> <?= t("close") ?></button>
    </div>
  </form>

<?php else: ?>

  <form action="<?= $action ?>" method="POST">
    <?= $this->renderCSRFToken($action) ?>
    <fieldset>
      <legend><?= $comment->id ? t("comments.edit.title") : t("comments.new.title") ?></legend>
      <?= $this->renderErrors() ?>

      <?php if (!$comment->id && isset($parent_comment_id) && $parent_comment_id): ?>
        <input type="hidden" name="comment[parent_comment_id]" value="<?= htmlspecialchars($parent_comment_id) ?>">
      <?php endif; ?>

      <?= $this->renderTextarea($comment, "body") ?>

      <button class="button"><?= $this->renderIcon($comment->id ? 'pencil-square' : 'plus-circle') ?> <?= $comment->id ? t("update") : t("create") ?></button>
      <a href="/admin/<?= $resource_type ?>/<?= $resource_id ?>" class="button"><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
    </fieldset>
  </form>

<?php endif; ?>
