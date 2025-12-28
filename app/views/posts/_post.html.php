<li>
  <?php
  $class = (isset($id) && $id == $post->id) ? "class='active'" : "";
  ?>
  <a <?= $class ?> href="/posts/<?= $post->id ?>"><?= $post->name ?></a>
</li>
