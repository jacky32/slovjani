<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("admin/votings/_form", [
  'voting' => isset($voting) ? $voting : new Voting(),
  'errors' => isset($errors) ? $errors : []
]) ?>
