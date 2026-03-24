<?= $this->renderPartial("admin/{$resource_type}/_left_pane", ["{$resource_type}" => $resources, 'id' => $resource_id, 'errors' => isset($errors) ? $errors : []]) ?>
<?= $this->renderPartial("admin/attachments/_form", [
  'resource' => $resource,
  'attachment' => $attachment,
  'errors' => isset($errors) ? $errors : [],
  'resource_type' => $resource_type,
  'resource_id' => $resource_id,
]) ?>
