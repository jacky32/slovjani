<?= $this->renderPartial("admin/users/_left_pane", ['users' => $users, "id" => $user->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= htmlspecialchars($user->username ?? '') ?>
  </h1>

  <div class="show-section">
    <span class="record-meta">
      <?php if ($user->created_at): ?>
        <?= t("created_at") ?>: <?= (new DateTime($user->created_at))->format('d.m.Y H:i') ?><br>
      <?php endif; ?>
      <?php if ($user->updated_at): ?>
        <?= t("updated_at") ?>: <?= (new DateTime($user->updated_at))->format('d.m.Y H:i') ?><br>
      <?php endif; ?>
      <?= t("attributes.user.email") ?>: <?= htmlspecialchars($user->email ?? '') ?><br>
      <?= t("attributes.user.role") ?>: <?= $user->roles_mask == \Delight\Auth\Role::ADMIN ? t("enums.user_roles.ADMIN") : ($user->roles_mask == \Delight\Auth\Role::COLLABORATOR ? t("enums.user_roles.COLLABORATOR") : t("enums.user_roles.NONE")) ?>
    </span>
    <div class="action-buttons show-section__actions">
      <a href='/admin/users/<?= $user->id ?>/edit' class='button'><?= $this->renderIcon('pencil-square') ?> <?= t("edit") ?></a>
      <?= $this->renderDestroyButton($user) ?>
    </div>
  </div>

  <div class="show-section">
    <h3 class="show-section__title"><?= t("attachments.index.title") ?></h3>
    <?php foreach ($user->attachments->get() as $attachment): ?>
      <div class="attachment-row">
        <a href='/admin/users/<?= $user->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $this->renderIcon('paper-clip') ?> <?= htmlspecialchars($attachment->visible_name) ?></a>
        <form action='/admin/users/<?= $user->id ?>/attachments/<?= $attachment->id ?>/destroy' method='POST'>
          <?= $this->renderCSRFToken("/admin/users/{$user->id}/attachments/{$attachment->id}/destroy") ?>
          <button class='button button--danger' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
        </form>
      </div>
    <?php endforeach; ?>
    <div class="show-section__add-action">
      <a href='/admin/users/<?= $user->id ?>/attachments/new' class='button'><?= $this->renderIcon('paper-clip') ?> <?= t("attachments.new.title") ?></a>
    </div>
  </div>

  <?= $this->renderPartial("admin/comments/_index", [
    'comments_collection' => $user->comments,
    'resource_type'       => 'users',
    'resource_id'         => $user->id,
  ]) ?>

</section>
