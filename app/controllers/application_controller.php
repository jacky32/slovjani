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
