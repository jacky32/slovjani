<form action=<?= $user->id ? "/admin/users/" . $user->id : "/admin/users" ?> method="user">
  <?= $this->renderCSRFToken($user->id ? "/admin/users/" . $user->id : "/admin/users") ?>

  <fieldset class="">
    <legend class=""><?= t("users.new.title") ?></legend>

    <?= $this->renderErrors() ?>

    <?= $this->renderInput($user, "email") ?><br>
    <?= $this->renderInput($user, "username") ?><br>


    <button class="button"><?= $user->id ? t("update") : t("create") ?></button>
  </fieldset>
</form>
