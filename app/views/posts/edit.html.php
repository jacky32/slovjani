<?= $this->renderPartial("posts/_left_pane", ['posts' => $posts, "id" => $post->id, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("posts/_form", ['post' => $post, 'errors' => isset($errors) ? $errors : []]) ?>
