  <section class='hero'>
    <div class='flex-col hero-content'>
      <?php $this->renderPartial("posts/_form", isset($errors) ? ['errors' => $errors] : []) ?>
      <ul class="mt-4 shadow-md list bg-base-100 rounded-box w-lg">
        <li class="p-4 pb-2 text-xs tracking-wide opacity-60"><?= t("menu.posts") ?></li>
        <?php
        if (count($posts) == 0) {
          echo "<li class='list-row'>
              <div class='text-xs font-semibold uppercase opacity-60'>
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
