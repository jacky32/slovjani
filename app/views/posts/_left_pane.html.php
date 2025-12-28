<section id="leftpane">
  <div class=''>
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
          $this->renderPartial('posts/_post', ['post' => $post, 'id' => isset($id) ? $id : null]);
        }
      }
      ?>
    </ul>
  </div>
</section>
