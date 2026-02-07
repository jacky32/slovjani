<?= $this->renderPartial("admin/posts/_left_pane", ['posts' => $posts, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("admin/posts/_form", [
  'post' => isset($post) ? $post : new Post(),
  'errors' => isset($errors) ? $errors : []
]) ?>
