<li>
  <?php
  $class = (isset($id) && $id == $user->id) ? "class='active'" : "";
  ?>
  <?= $this->renderPartial('layouts/pagination/_order_number', ['index' => $index]) ?>
  <a <?= $class ?> href="/admin/users/<?= $user->id ?>"><?= $user->username ?></a>
</li>
