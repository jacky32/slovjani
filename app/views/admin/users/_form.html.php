<section id="rightpane">
  <h1><?= t("users.new.title") ?></h1>

  <div class="show-section">
    <form action="<?= $user->id ? "/admin/users/" . $user->id : "/admin/users" ?>" method="POST">
      <?= $this->renderCSRFToken($user->id ? "/admin/users/" . $user->id : "/admin/users") ?>

      <?= $this->renderErrors() ?>

      <?= $this->renderInput($user, "email") ?><br>
      <?= $this->renderInput($user, "username") ?><br>
      <?php if (!$user->id): ?>
        <?= $this->renderInput($user, "password", "password") ?><br>
      <?php endif; ?>

      <label for="status-select"><?= User::humanAttributeName("role") ?></label>
      <select id="status-select" name="user[role]">
        <option value="admin" <?= $user->roles_mask == \Delight\Auth\Role::ADMIN ? 'selected' : '' ?>><?= t("enums.user_roles.ADMIN") ?></option>
        <option value="collaborator" <?= $user->roles_mask == \Delight\Auth\Role::COLLABORATOR ? 'selected' : '' ?>><?= t("enums.user_roles.COLLABORATOR") ?></option>
        <option value="none" <?= $user->roles_mask == 0 ? 'selected' : '' ?>><?= t("enums.user_roles.NONE") ?></option>
      </select>

      <div class="action-buttons show-section__actions">
        <button class="button"><?= $this->renderIcon($user->id ? 'pencil-square' : 'plus-circle') ?> <?= $user->id ? t("update") : t("create") ?></button>
        <a href='<?= $user->id ? "/admin/users/{$user->id}" : "/admin/users" ?>' class='button'><?= $this->renderIcon('x-mark') ?> <?= t("cancel") ?></a>
      </div>
    </form>
  </div>
</section>
