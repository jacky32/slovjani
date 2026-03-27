<?= $this->renderPartial("admin/posts/_left_pane", ['posts' => $posts, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <br><br>
  <a href='/admin/posts/new' class='button'><?= $this->renderIcon('plus-circle') ?> <?= t("posts.new.title") ?></a>
</section>
