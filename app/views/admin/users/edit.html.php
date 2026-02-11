<?= $this->renderPartial("admin/users/_left_pane", ['users' => $users, "id" => $user->id, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("admin/users/_form", ['user' => $user, 'errors' => isset($errors) ? $errors : []]) ?>
