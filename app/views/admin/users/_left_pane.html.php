<section id="leftpane">
  <div class=''>
    <ul class="listarticles">
      <li><?= t("menu.users") ?></li>
      <li>
        <?= $this->renderPartial('layouts/pagination/_previous_button') ?>
      </li>
      <?php if (count($users) == 0) : ?>
        <li>
          <div>
            <?= t("users.index.no_users_found") ?>
          </div>
        </li>
      <?php else : ?>
        <?php foreach ($users as $index => $user) : ?>
          <?= $this->renderPartial('admin/users/_user', ['user' => $user, 'id' => isset($id) ? $id : null, 'index' => $index]) ?>
        <?php endforeach; ?>
      <?php endif; ?>
      <?= $this->renderPartial('layouts/pagination/_bottom_pagination') ?>
    </ul>
  </div>
</section>
