<li>
  <?php
  $class = (isset($id) && $id == $event->id) ? "class='active'" : "";
  ?>
  <?= $this->renderPartial('layouts/pagination/_order_number', ['index' => $index]) ?>
  <a <?= $class ?> href="/events/<?= $event->id ?>"><?= $event->name ?></a>
</li>
