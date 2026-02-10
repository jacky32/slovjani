<section id="leftpane">
  <div class=''>
    <ul class="listarticles">
      <li><?= t("menu.posts") ?></li>
      <li>
        <?= $this->renderPartial('layouts/pagination/_previous_button') ?>
      </li>
      <?php if (count($posts) == 0) : ?>
        <li>
          <div>
            <?= t("posts.index.no_posts_found") ?>
          </div>
        </li>
      <?php else : ?>
        <?php foreach ($posts as $index => $post) : ?>
          <?= $this->renderPartial('admin/posts/_post', ['post' => $post, 'id' => isset($id) ? $id : null, 'index' => $index]) ?>
        <?php endforeach; ?>
      <?php endif; ?>
      <?= $this->renderPartial('layouts/pagination/_bottom_pagination') ?>
    </ul>
  </div>
</section>
