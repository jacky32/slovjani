<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\FlashManager;
use App\Services\RecaptchaService;
use App\Services\ViewManager;

/**
 * Base controller that provides shared auth, rendering, CSRF, and flash helpers.
 *
 * @package Controllers
 */
class ApplicationController
{
  protected $errors = [];
  protected $viewManager;
  protected $auth;
  protected \PDO $connection;
  protected ?RecaptchaService $recaptchaService = null;

  /**
   * Initialises the controller: opens a PDO connection, creates the Auth
   * service and the ViewManager.
   */
  public function __construct()
  {
    // TODO: close the PDO connection?
    $this->connection = new \PDO("mysql:host=" . getenv("MYSQL_HOST") . ";dbname=" . getenv("MYSQL_DATABASE"), getenv("MYSQL_USER"), getenv("MYSQL_PASSWORD"));
    $this->auth = new \Delight\Auth\Auth($this->connection);
    $this->recaptchaService = new RecaptchaService();
    $this->viewManager = new ViewManager($this->auth);

    // set_exception_handler(function (\Exception $exception) {
    //   error_log("  ");
    //   error_log($exception->getMessage());
    //   error_log("  ");
    //   $this->addFlash('error', $exception->getMessage());
    // });
  }

  /**
   * Verifies the CSRF token submitted with a form.
   * Regenerates the session token after each successful check.
   *
   * @param string $formAction The form action path used to generate and verify the HMAC token.
   * @throws \Exception If the token is missing or does not match.
   */
  protected function verifyCSRF($formAction)
  {
    // CSRF protection
    if (empty($_POST['token'])) {
      throw new \Exception(t("errors.csrf_token_missing"));
    }
    $calc = hash_hmac('sha256', $formAction, $_SESSION['token']);
    $_SESSION['token'] = bin2hex(random_bytes(32)); // Regenerate token after use
    if (!hash_equals($calc, $_POST['token'])) {
      throw new \Exception(t("errors.csrf_token_invalid"));
    }
  }

  /**
   * Adds a flash message to the session via FlashManager.
   *
   * @param string $type    Flash type: 'success', 'error', 'info', or 'warning'.
   * @param string $message The message text.
   * @return void
   */
  protected function addFlash($type, $message)
  {
    FlashManager::addFlash($type, $message);
  }

  /**
   * Renders a view template through the ViewManager.
   *
   * @param string $view The view name relative to app/views (without .html.php extension).
   * @param array  $data Associative array of variables to pass to the view.
   * @return void
   */
  protected function render($view, $data = [])
  {
    $this->viewManager->render($view, $data);
  }
}

class_alias(__NAMESPACE__ . '\\ApplicationController', 'ApplicationController');
