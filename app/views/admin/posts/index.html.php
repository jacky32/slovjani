<?= $this->renderPartial("admin/posts/_left_pane", ['posts' => $posts, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  index stránka příspěvků
  <br><br>
  <a href='/admin/posts/new' class='button'><?= t("posts.new.title") ?></a>
</section>
