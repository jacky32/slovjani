<?= $this->renderPartial("admin/events/_left_pane", ['events' => $events, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("admin/events/_form", [
  'event' => $event,
  'errors' => isset($errors) ? $errors : []
]) ?>
