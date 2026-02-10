<li>
  <?php
  $class = (isset($id) && $id == $voting->id) ? "class='active'" : "";
  ?>
  <?= $this->renderPartial('layouts/pagination/_order_number', ['index' => $index]) ?>
  <a <?= $class ?> href="/admin/votings/<?= $voting->id ?>"><?= $voting->name ?></a>
</li>
