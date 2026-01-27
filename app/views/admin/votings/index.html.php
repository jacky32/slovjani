<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  index stránka hlasování
  <br><br>
  <a href='/admin/votings/new' class='button'><?= t("votings.new.title") ?></a>
</section>
