<?php if ($this->pagination && $this->pagination->next_page) : ?>
  <a class="button" style="color:#FFF;" href="?page=<?= $this->pagination->next_page ?>"><?= t("pagination.following") ?></a>
<?php endif; ?>
