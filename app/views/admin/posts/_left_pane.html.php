<section id="leftpane">
  <div class=''>
    <ul class="listarticles">
      <li><?= t("menu.posts") ?></li>
      <li>
        <?php if ($this->pagination && $this->pagination->previous_page) : ?>
          <a class="button" style="color:#FFF;" href="?page=<?= $this->pagination->previous_page ?>"><?= t("pagination.previous") ?></a>
        <?php endif; ?>
      </li>
      <?php
      if (count($posts) == 0) {
        echo "<li >
            <div >
              " . t("posts.index.no_posts_found") . "
            </div>
          </li>";
      } else {
        foreach ($posts as $index => $post) {
          $this->renderPartial('admin/posts/_post', ['post' => $post, 'id' => isset($id) ? $id : null, 'index' => $index]);
        }
      }
      ?>
      <?php if ($this->pagination) : ?>
        <?php if ($this->pagination->next_page): ?>
          <li>
            <a class="button" style="color:#FFF;" href="?page=<?= $this->pagination->next_page ?>"><?= t("pagination.following") ?></a>
          </li>
        <?php endif; ?>
        <li>
          <span><?= t("pagination.page_info", ["current_page" => $this->pagination->current_page, "total_pages" => $this->pagination->total_pages]) ?></span>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</section>
