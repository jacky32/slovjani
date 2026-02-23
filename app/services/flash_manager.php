<?php
/**
 * @package Services
 */
class FlashManager
{
  public static function addFlash($type, $message)
  {
    if (!in_array($type, ['success', 'error', 'info', 'warning'])) {
      throw new InvalidArgumentException("Invalid flash type: $type");
    }
    if (!isset($_SESSION['FLASHES'])) {
      $_SESSION['FLASHES'] = [];
    }
    if (!isset($_SESSION['FLASHES'][$type])) {
      $_SESSION['FLASHES'][$type] = [];
    }
    $_SESSION['FLASHES'][$type][] = $message;
  }

  public static function getFlashes()
  {
    if (!isset($_SESSION['FLASHES'])) {
      return [];
    }
    $flashes = $_SESSION['FLASHES'];
    $_SESSION['FLASHES_DISPLAY_COUNT'] = isset($_SESSION['FLASHES_DISPLAY_COUNT']) ? $_SESSION['FLASHES_DISPLAY_COUNT'] + 1 : 1;
    // TODO: Flash management
    if ($_SESSION['FLASHES_DISPLAY_COUNT'] >= 2) {
      unset($_SESSION['FLASHES']);
      unset($_SESSION['FLASHES_DISPLAY_COUNT']);
    }
    return $flashes;
  }

  public static function hasFlashes()
  {
    return isset($_SESSION['FLASHES']) && count($_SESSION['FLASHES']) > 0;
  }
}
