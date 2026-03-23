<?php if ($this->pagination && $this->pagination->next_page) : ?>
  <a class="button" style="color:#FFF;" href="?page=<?= $this->pagination->next_page ?>" aria-label="<?= t('pagination.next_page_label', ['page' => $this->pagination->next_page]) ?>"><?= t("pagination.following") ?> <?= $this->renderIcon('chevron-right') ?></a>
<?php endif; ?>
