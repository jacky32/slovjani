<?php
class ApplicationController
{
  protected $errors = [];
  protected $viewManager;
  protected $auth;

  public function __construct()
  {
    $conn = new PDO("mysql:host=" . getenv("MYSQL_HOST") . ";dbname=" . getenv("MYSQL_DATABASE"), getenv("MYSQL_USER"), getenv("MYSQL_PASSWORD"));
    $this->auth = new \Delight\Auth\Auth($conn);
    $this->viewManager = new ViewManager($this->auth);

    // set_exception_handler(function (Exception $exception) {
    //   error_log("  ");
    //   error_log($exception->getMessage());
    //   error_log("  ");
    //   $this->addFlash('error', $exception->getMessage());
    // });
  }

  protected function verifyCSRF($formAction)
  {
    // CSRF protection
    if (empty($_POST['token'])) {
      throw new Exception("CSRF token is missing.");
    }
    $calc = hash_hmac('sha256', $formAction, $_SESSION['token']);
    $_SESSION['token'] = bin2hex(random_bytes(32)); // Regenerate token after use
    if (!hash_equals($calc, $_POST['token'])) {
      throw new Exception("Invalid CSRF token.");
    }
  }

  protected function addFlash($type, $message)
  {
    FlashManager::addFlash($type, $message);
  }

  protected function render($view, $data = [])
  {
    $this->viewManager->render($view, $data);
  }
}
