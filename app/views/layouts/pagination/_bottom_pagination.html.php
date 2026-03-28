<?php if ($this->pagination) : ?>
  <li>
    <?= $this->renderPartial('layouts/pagination/_next_button') ?>
  </li>
  <li>
    <?= $this->renderPartial('layouts/pagination/_total_pages') ?>
  </li>
<?php endif; ?>
