<div class="comment-item <?= ($depth ?? 0) > 0 ? 'comment-item--child' : '' ?>">
  <small>
    <?= htmlspecialchars($comment->creator->username ?? '—') ?>
    <?php if ($comment->created_at): ?>
      &middot; <?= (new DateTime($comment->created_at))->format('d.m.Y H:i') ?>
    <?php endif; ?>
  </small>
  <p class="comment-body"><?= nl2br(htmlspecialchars($comment->body ?? '')) ?></p>
  <div class="comment-actions">
    <details class="comment-form-toggle">
      <summary class="button"><?= $this->renderIcon('pencil-square') ?> <?= t("edit") ?></summary>
      <?= $this->renderPartial("admin/comments/_form", [
        'comment'       => $comment,
        'resource_type' => $resource_type,
        'resource_id'   => $resource_id,
        'inline'        => true,
      ]) ?>
    </details>
    <?= $this->renderPartial("admin/comments/_form", [
      'comment'       => $comment,
      'resource_type' => $resource_type,
      'resource_id'   => $resource_id,
      'destroy'       => true,
    ]) ?>
    <?php if (($depth ?? 0) < 1): ?>
      <details class="comment-form-toggle">
        <summary class="button"><?= $this->renderIcon('chat-bubble-left-ellipsis') ?> <?= t("reply") ?></summary>
        <?= $this->renderPartial("admin/comments/_form", [
          'comment'           => new Comment(),
          'resource_type'     => $resource_type,
          'resource_id'       => $resource_id,
          'parent_comment_id' => $comment->id,
          'inline'            => true,
        ]) ?>
      </details>
    <?php endif; ?>
  </div>

  <?php if (($depth ?? 0) < 1): ?>
    <?php foreach ($all_comments as $reply): ?>
      <?php if ($reply->parent_comment_id == $comment->id): ?>
        <?= $this->renderPartial("admin/comments/_comment", [
          'comment'      => $reply,
          'all_comments' => $all_comments,
          'resource_type' => $resource_type,
          'resource_id'   => $resource_id,
          'depth'         => ($depth ?? 0) + 1,
        ]) ?>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
