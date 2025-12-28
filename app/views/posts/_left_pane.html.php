<section id="leftpane">
  <div class=''>
    <!-- <?php $this->renderPartial("posts/_form", isset($errors) ? ['errors' => $errors] : []) ?> -->
    <ul class="listarticles">
      <li><?= t("menu.posts") ?></li>
      <?php
      if (count($posts) == 0) {
        echo "<li >
            <div >
              " . t("posts.index.no_posts_found") . "
            </div>
          </li>";
      } else {
        foreach ($posts as $post) {
          $this->renderPartial('posts/_post', ['post' => $post]);
        }
      }
      ?>
    </ul>
  </div>
</section>
