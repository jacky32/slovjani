<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, "id" => $voting->id, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("admin/votings/_form", ['voting' => $voting, 'errors' => isset($errors) ? $errors : []]) ?>
