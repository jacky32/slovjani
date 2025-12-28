<?php
if (FlashManager::hasFlashes()) {
  $flashes = FlashManager::getFlashes();
  foreach ($flashes as $type => $messages) {
    echo '<div class="flash">';
    echo '  <div class="alert-' . $type . '">';
    echo '    <button type="button">';
    echo '      <span><i></i></span>';
    echo '    </button>';
    foreach ($messages as $message) {
      echo "<span>$message</span><br />";
    }
    echo '  </div>';
    echo '</div>';
  }
}
