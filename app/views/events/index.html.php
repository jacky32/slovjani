<?= $this->renderPartial("events/_left_pane", ['events' => $events, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <?= t("events.index.placeholder") ?>
  <br><br>
</section>
