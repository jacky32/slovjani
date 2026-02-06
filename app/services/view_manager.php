<?php
class ViewManager
{
  private $content;
  private $controller;
  private $action;
  private $title;
  private $controllerData;
  private $auth;
  private $errors;

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
    $this->errors = $data['errors'] ?? [];
    ob_start();
    $this->title = isset($title) ? $title : 'Slované';
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
    Logger::debug("Rendered partial: app/views/$filename.html.php");
    return;
  }

  public function renderErrors()
  {
    if (isset($this->errors) && !empty($this->errors)) {
      $this->renderPartial("layouts/forms/_errors", ['errors' => $this->errors]);
    }
    return;
  }

  public function isAttributeInvalid($attribute)
  {
    if (isset($this->errors) && !empty($this->errors)) {
      foreach ($this->errors as $error) {
        if ($error['attribute'] === $attribute) {
          return "warning";
        }
      }
    }
    return "";
  }

  public function renderTextarea($object, $attribute, $required = true)
  {
    $placeholder = t("attributes." . strtolower($object::class) . "." . $attribute);
    $name = toSnakeCase($object::class) . "[" . $attribute . "]";
    $class = "";

    if (isset($this->errors) && !empty($this->errors)) {
      foreach ($this->errors as $error) {
        if ($error['attribute'] === $attribute) {
          $class = "warning";
          break;
        }
      }
    }
    return "<textarea class='" . $class . "' placeholder='" . $placeholder . "' name='" . $name . "' " . ($required ? "required" : "") . ">" . htmlspecialchars($object->{$attribute} ?? '') . "</textarea>";
  }

  public function renderInput($object, $attribute, $type = "text", $required = true)
  {
    $placeholder = t("attributes." . strtolower($object::class) . "." . $attribute);
    $name = toSnakeCase($object::class) . "[" . $attribute . "]";
    $value = htmlspecialchars($object->{$attribute} ?? '');
    $class = "";

    if (isset($this->errors) && !empty($this->errors)) {
      foreach ($this->errors as $error) {
        if ($error['attribute'] === $attribute) {
          $class = "warning";
          break;
        }
      }
    }
    return "<input type='" . $type . "' class='" . $class . "' placeholder='" . $placeholder . "' name='" . $name . "' value='" . $value . "' " . ($required ? "required" : "") . " />";
  }

  public function renderCSRFToken($formAction)
  {
    ob_start();
    include 'app/views/layouts/forms/_csrf_token.html.php';
    echo ob_get_clean();
    return;
  }

  public function __destruct()
  {
    include 'app/views/layouts/application.html.php';
  }
}
