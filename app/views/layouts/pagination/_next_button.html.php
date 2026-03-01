<?php if ($this->pagination && $this->pagination->next_page) : ?>
  <a class="button" style="color:#FFF;" href="?page=<?= $this->pagination->next_page ?>"><?= t("pagination.following") ?> <?= $this->renderIcon('chevron-right') ?></a></a>
<?php endif; ?>
