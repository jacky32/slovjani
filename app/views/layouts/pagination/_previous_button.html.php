<?php if ($this->pagination && $this->pagination->previous_page) : ?>
  <a class="button" style="color:#FFF;" href="?page=<?= $this->pagination->previous_page ?>"><?= $this->renderIcon('chevron-left') ?> <?= t("pagination.previous") ?></a>
<?php endif; ?>
