<?= $this->renderPartial("posts/_left_pane", ['posts' => $posts, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("posts/_form", ['post' => isset($post) ? $post : null, 'errors' => isset($errors) ? $errors : []]) ?>
