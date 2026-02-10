<?php if ($this->pagination) : ?>
  <strong><?= $index + 1 +  (($this->pagination->current_page - 1) * $this->pagination->per_page) ?>.</strong>
<?php endif; ?>
