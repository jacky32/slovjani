<?php if (\App\Services\FlashManager::hasFlashes()) : ?>
  <?php $flashes = \App\Services\FlashManager::getFlashes(); ?>
  <div class="flash-container" role="status" aria-live="polite" aria-atomic="true">
    <?php foreach ($flashes as $type => $messages) : ?>
      <?php foreach ($messages as $message) : ?>
        <div class="flash alert-<?= htmlspecialchars($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>">
          <p><?= htmlspecialchars($message) ?></p>
          <button type="button" class="close" aria-label="<?= t('close') ?>"><?= $this->renderIcon('x-mark') ?></button>
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
        setTimeout(() => container.remove(), 15000);
      }
    }
  </script>
<?php endif; ?>
