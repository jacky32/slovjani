<li>
  <?php
  $class = (isset($id) && $id == $voting->id) ? "class='active'" : "";
  ?>
  <a <?= $class ?> href="/admin/votings/<?= $voting->id ?>"><?= $voting->name ?></a>
</li>
