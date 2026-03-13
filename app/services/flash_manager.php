<?php

/**
 * Session-backed flash message manager for user-facing notifications.
 *
 * @package Services
 */
class FlashManager
{
  /**
   * Appends a flash message to the session store.
   *
   * @param string $type    Message type: 'success', 'error', 'info', or 'warning'.
   * @param string $message The message text.
   * @return void
   * @throws \InvalidArgumentException If $type is not one of the allowed values.
   */
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

  /**
   * Retrieves all queued flash messages and increments the display counter.
   * Clears the flash store after it has been displayed.
   *
   * @return array Associative array keyed by type containing arrays of message strings.
   */
  public static function getFlashes()
  {
    if (!isset($_SESSION['FLASHES'])) {
      return [];
    }
    $flashes = $_SESSION['FLASHES'];
    $_SESSION['FLASHES_DISPLAY_COUNT'] = isset($_SESSION['FLASHES_DISPLAY_COUNT']) ? $_SESSION['FLASHES_DISPLAY_COUNT'] + 1 : 1;
    if ($_SESSION['FLASHES_DISPLAY_COUNT'] >= 2) {
      unset($_SESSION['FLASHES']);
      unset($_SESSION['FLASHES_DISPLAY_COUNT']);
    }
    return $flashes;
  }

  /**
   * Returns whether there are any pending flash messages in the session.
   *
   * @return bool True if at least one flash message is queued.
   */
  public static function hasFlashes()
  {
    return isset($_SESSION['FLASHES']) && count($_SESSION['FLASHES']) > 0;
  }
}
