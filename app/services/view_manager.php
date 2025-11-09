<?php
class ViewManager
{
  private $content;
  private $controller;
  private $action;
  private $title;
  private $controllerData;
  private $auth;

  public function __construct($auth)
  {
    $this->auth = $auth;
    $tmp = debug_backtrace();
    $this->controller = str_replace("controller", "", strtolower($tmp[1]['class']));
    $this->action = str_replace("action", "", strtolower($tmp[1]['function']));
  }

  public function render($view, $data = [])
  {
    $this->controllerData = extract($data);
    ob_start();
    $this->title = isset($title) ? $title : 'název';
    include "app/views/$view.html.php";
    $this->content = ob_get_contents();
    ob_end_clean();
    return $this->content;
  }

  public function renderPartial($filename, $vars = null)
  {
    if (is_array($vars) && !empty($vars)) {
      extract($vars);
    }
    ob_start();
    include "app/views/$filename.html.php";
    echo ob_get_clean();
    return;
  }

  public function renderCSRFToken($formAction)
  {
    ob_start();
    include 'app/views/layouts/_csrf_token.html.php';
    echo ob_get_clean();
    return;
  }

  public function __destruct()
  {
    include 'app/views/layouts/application.html.php';
  }
}
