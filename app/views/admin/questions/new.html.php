<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, 'id' => $voting->id, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("admin/questions/_form", [
  'voting' => $voting,
  'question' => isset($question) ? $question : new Question(),
  'errors' => isset($errors) ? $errors : []
]) ?>
