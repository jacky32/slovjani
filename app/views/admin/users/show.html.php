<?= $this->renderPartial("admin/users/_left_pane", ['users' => $users, "id" => $user->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= htmlspecialchars($user->username ?? '') ?>
  </h1>
  <small>
    <?php
    if ($user->created_at) {
      $date = new DateTime($user->created_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("created_at") . ": " . $formatted;
    }
    ?><br>
    <?php
    if ($user->updated_at) {
      $date = new DateTime($user->updated_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("updated_at") . ": " . $formatted;
    }
    ?><br>
    <?= t("attributes.user.email") ?>: <?= htmlspecialchars($user->email ?? '') ?><br>
    <?= t("attributes.user.role") ?>: <?= $user->roles_mask == \Delight\Auth\Role::ADMIN ? t("enums.user_roles.ADMIN") : ($user->roles_mask == \Delight\Auth\Role::COLLABORATOR ? t("enums.user_roles.COLLABORATOR") : t("enums.user_roles.NONE")) ?><br>
  </small>

  <div class="action-buttons">
    <a href='/admin/users/<?= $user->id ?>/edit' class='button'><?= $this->renderIcon('pencil-square') ?> <?= t("edit") ?></a>
    <?= $this->renderDestroyButton($user) ?>
  </div>


  <h3><?= t("attachments.index.title") ?></h3>
  <?php foreach ($user->attachments->get() as $attachment): ?>
    <div class="attachment-row">
      <a href='/admin/users/<?= $user->id ?>/attachments/<?= $attachment->id ?>' target="_blank" class='button'><?= $this->renderIcon('paper-clip') ?> <?= $attachment->visible_name ?></a>
      <form action='/admin/users/<?= $user->id ?>/attachments/<?= $attachment->id ?>/destroy' method='POST'>
        <?= $this->renderCSRFToken("/admin/users/{$user->id}/attachments/{$attachment->id}/destroy") ?>
        <button class='button button--danger' type='submit'><?= $this->renderIcon('trash') ?> <?= t("delete") ?></button>
      </form>
    </div>
  <?php endforeach; ?>
  <br>
  <a href='/admin/users/<?= $user->id ?>/attachments/new' class='button'><?= $this->renderIcon('paper-clip') ?> <?= t("attachments.new.title") ?></a><br>

  <?= $this->renderPartial("admin/comments/_index", [
    'comments_collection' => $user->comments,
    'resource_type'       => 'users',
    'resource_id'         => $user->id,
  ]) ?>

</section>
