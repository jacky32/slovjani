<?php
/**
 * @package Services
 */
class ViewManager
{
  private $content;
  private $controller;
  private $action;
  private $title;
  private $controllerData;
  private $auth;
  private $errors;
  private $pagination;

  public function __construct($auth)
  {
    $this->auth = $auth;
    $tmp = debug_backtrace();
    $this->controller = $tmp[2]['class'];
    if ($this->controller == "AdminController") {
      $this->controller = $tmp[3]['class'];
    }
  }

  public function render($view, $data = [])
  {
    $this->controllerData = extract($data);
    $this->pagination = $data['pagination'] ?? null;
    $this->errors = $data['errors'] ?? [];
    ob_start();
    $this->title = isset($title) ? $title : 'Slované';
    include "app/views/$view.html.php";
    $this->content = ob_get_clean();
    return $this->content;
  }

  /**
   * Render a partial view with the given variables.
   * @param string $filename The filename of the partial view, relative to the app/views directory, without the .html.php extension. For example, "layouts/forms/_errors" would include the file app/views/layouts/forms/_errors.html.php.
   * @param array|null $vars An associative array of variables to extract and make available to the partial view. For example, ['errors' => $this->errors] would make an $errors variable available in the partial view. If null or empty, no additional variables are extracted.
   * @return void Echoes the rendered partial view.
   */
  public function renderPartial(string $filename, ?array $vars = null): void
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

  /**
   * Render validation errors for the current view. If there are any errors in the $this->errors property,
   * it renders the "layouts/forms/_errors" partial view and passes the errors to it.
   * If there are no errors, it does nothing.
   * @return void Echoes the rendered errors partial view if there are errors, otherwise does nothing.
   */
  public function renderErrors(): void
  {
    if (isset($this->errors) && !empty($this->errors)) {
      $this->renderPartial("layouts/forms/_errors", ['errors' => $this->errors]);
    }
    return;
  }

  /**
   * Renders a textarea form field for a given object's attribute, with optional validation error highlighting.
   * @param object $object The object whose attribute is being rendered (e.g. a Post, Voting, etc.)
   * @param string $attribute The name of the attribute to render (e.g. "name", "description", etc.)
   * @param bool $required Whether the field is required (default: true). If true, the "required" attribute will be added to the textarea.
   * @return string The HTML string for the textarea form field.
   */
  public function renderTextarea(ActiveModel $object, string $attribute, bool $required = true): string
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

  /**
   * Renders an input form field for a given object's attribute, with optional validation error highlighting.
   * @param object $object The object whose attribute is being rendered (e.g. a Post, Voting, etc.)
   * @param string $attribute The name of the attribute to render (e.g. "name", "description", etc.)
   * @param string $type The type of the input field (e.g. "text", "email", "password", etc.). Default is "text".
   * @param bool $required Whether the field is required (default: true). If true, the "required" attribute will be added to the input.
   * @return string The HTML string for the input form field.
   */
  public function renderInput(ActiveModel $object, string $attribute, string $type = "text", bool $required = true): string
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

  /**
   * Render a destroy button for a simple path
   * e.g. /posts/:id/destroy, /votings/:id/destroy, etc.
   * Only renders if the current user is the creator of the object
   * Uses CSRF token for protection
   * @param ActiveModel $object The object for which to render the destroy button
   * @return string The HTML for the destroy button, or an empty string if the user is not the creator of the object
   */
  public function renderDestroyButton(ActiveModel $object): string
  {
    if ($object->creator_id == $this->auth->getUserId()) {
      $path = "/" . toSnakeCase($object::class) . "s/" . $object->id . "/destroy";
      return "<form action='" . $path . "' method='POST'>" .
        "<input type='hidden' name='token' value='" . hash_hmac('sha256', $path, $_SESSION['token']) . "' />" .
        "<input type='hidden' name='id' value='" . $object->id . "' />" .
        "<button class='button' type='submit'>" . t("delete") . "</button>" .
        "</form>";
    }
    return "";
  }

  /**
   * Render a CSRF token input for a form
   * Usage: call $this->renderCSRFToken('/posts') inside a form that submits to /posts,
   * or $this->renderCSRFToken('/posts/' . $post->id) for a form that submits to /posts/:id, etc.
   * The token is generated using HMAC with the form action as the message and the session token as the key,
   * and is included as a hidden input in the form. The server can then verify the token by regenerating it
   * and comparing it to the submitted token.
   * @param string $formAction The action URL of the form, used to generate the token
   * @return void Echoes the HTML for the CSRF token input
   */
  public function renderCSRFToken(string $formAction): void
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
