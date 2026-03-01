<?php if (FlashManager::hasFlashes()) : ?>
  <?php $flashes = FlashManager::getFlashes(); ?>
  <div class="flash-container">
    <?php foreach ($flashes as $type => $messages) : ?>
      <?php foreach ($messages as $message) : ?>
        <div class="flash alert-<?= htmlspecialchars($type) ?>" role="alert">
          <p><?= htmlspecialchars($message) ?></p>
          <button type="button" class="close" aria-label="Zavřít"><?= $this->renderIcon('x-mark') ?></button>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
  <script>
    {
      const container = document.querySelector('.flash-container');
      if (container) {
        container.querySelectorAll('.close').forEach(btn => {
          btn.addEventListener('click', () => {
            btn.closest('.flash')?.remove();
            if (!container.querySelector('.flash')) container.remove();
          });
        });
        setTimeout(() => container.remove(), 5000);
      }
    }
  </script>
<?php endif; ?>
