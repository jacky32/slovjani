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
  <a href='/admin/users/<?= $user->id ?>/edit' class='button'><?= t("edit") ?></a>

  <?= $this->renderDestroyButton($user) ?>
</section>
