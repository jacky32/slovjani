<?= $this->renderPartial("admin/users/_left_pane", ['users' => $users, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <?= t("users.index.placeholder") ?>
  <br><br>
  <!-- <a href='/admin/users/new' class='button'><?= t("users.new.title") ?></a> -->
</section>
