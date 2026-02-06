<?= $this->renderPartial("admin/events/_left_pane", ['events' => $events, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("admin/events/_form", [
  'event' => isset($event) ? $event : new Event(),
  'errors' => isset($errors) ? $errors : []
]) ?>
