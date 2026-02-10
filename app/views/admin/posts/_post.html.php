<li>
  <?php
  $class = (isset($id) && $id == $post->id) ? "class='active'" : "";
  ?>
  <?= $this->renderPartial('layouts/pagination/_order_number', ['index' => $index]) ?>
  <a <?= $class ?> href="/admin/posts/<?= $post->id ?>"><?= $post->name ?></a>
</li>
