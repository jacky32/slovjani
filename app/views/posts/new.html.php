<?= $this->renderPartial("posts/_left_pane", ['posts' => [], 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("posts/_form", isset($errors) ? ['errors' => $errors] : []) ?>
