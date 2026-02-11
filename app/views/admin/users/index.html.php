<?= $this->renderPartial("admin/users/_left_pane", ['users' => $users, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  index stránka kontaktů
  <br><br>
  <a href='/admin/users/new' class='button'><?= t("users.new.title") ?></a>
</section>
