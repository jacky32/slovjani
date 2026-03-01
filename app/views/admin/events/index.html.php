<?= $this->renderPartial("admin/events/_left_pane", ['events' => $events, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <?= t("events.index.admin_placeholder") ?>
  <br><br>
  <a href='/admin/events/new' class='button'><?= t("events.new.title") ?></a>
</section>
