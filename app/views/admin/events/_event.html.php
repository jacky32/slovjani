<li>
  <?php
  $class = (isset($id) && $id == $event->id) ? "class='active'" : "";
  ?>
  <a <?= $class ?> href="/admin/events/<?= $event->id ?>"><?= $event->name ?></a>
</li>
