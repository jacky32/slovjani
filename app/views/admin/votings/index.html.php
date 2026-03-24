<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <br><br>
  <a href='/admin/votings/new' class='button'><?= $this->renderIcon('plus-circle') ?> <?= t("votings.new.title") ?></a>
</section>
