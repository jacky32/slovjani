<?= $this->renderPartial("admin/events/_left_pane", ['events' => $events, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  index stránka admin činnosti
  <br><br>
  <a href='/admin/events/new' class='button'><?= t("events.new.title") ?></a>
</section>
