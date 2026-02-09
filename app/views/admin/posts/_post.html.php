<li>
  <?php
  $class = (isset($id) && $id == $post->id) ? "class='active'" : "";
  ?>

  <?php if ($this->pagination) : ?>
    <strong><?= $index + 1 +  (($this->pagination->current_page - 1) * $this->pagination->per_page) ?>.</strong>
  <?php endif; ?>
  <a <?= $class ?> href="/admin/posts/<?= $post->id ?>"><?= $post->name ?></a>
</li>
