<?= $this->renderPartial("posts/_left_pane", ['posts' => $posts, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  index stránka příspěvků
  <br><br>
  <a href='/posts/new' class='button'><?= t("posts.new.title") ?></a>
</section>
