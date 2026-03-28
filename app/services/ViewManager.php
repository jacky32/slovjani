<?php

declare(strict_types=1);

namespace App\Services;

/**
 * View rendering coordinator for templates, partials, form helpers, and layout output.
 *
 * @package Services
 */
class ViewManager
{
  private const VIEWS_ROOT = __DIR__ . '/../views';

  private $content;
  private $controller;
  private $action;
  private $title;
  private ?string $metaDescription = null;
  private ?string $metaDescriptionSource = null;
  private string $view = '';
  private $controllerData;
  private $auth;
  private $errors;
  private $pagination;
  private bool $shouldRenderLayout = true;


  /**
   * The constructor method for the ViewManager class.
   * It initializes the object with the given authentication service and determines the current controller based on the debug backtrace.
   * @param \Delight\Auth\Auth|GuestAuth $auth The authentication service (or a GuestAuth stub) to be used for rendering views.
   * @param string|null $controllerOverride When provided, skips the backtrace and uses this value as the active controller name. Used by StaticPageGenerator.
   */
  public function __construct(\Delight\Auth\Auth|GuestAuth $auth, ?string $controllerOverride = null)
  {
    $this->auth = $auth;
    if ($controllerOverride !== null) {
      $this->controller = $controllerOverride;
    } else {
      $this->controller = 'ApplicationController';
      $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

      foreach ($trace as $frame) {
        $class = $frame['class'] ?? null;
        if (!$class) {
          continue;
        }
        if ($class === __CLASS__) {
          continue;
        }
        if (str_ends_with($class, 'Controller')) {
          $this->controller = $class;
          break;
        }
      }
    }
  }

  /**
   * Renders a view with the given data. It extracts the data into variables,
   * captures the output of including the view file, and stores it in the $content property.
   * The view file is expected to be located in the app/views directory and have a .html.php extension.
   * The method also sets the page title and makes any errors or pagination data available to the view.
   * @param string $view The name of the view to render, relative to the app/views directory, without the .html.php extension. For example, "posts/index" would include the file app/views/posts/index.html.php.
   * @param array $data An associative array of data to be extracted into variables and made available to the view. For example, ['posts' => $posts] would make a $posts variable available in the view.
   * @return string The rendered content of the view, which is stored in the $content property and can be included in the layout.
   */
  public function render(string $view, array $data = []): string
  {
    $this->view = $view;
    $this->controllerData = extract($data);
    $this->pagination = $data['pagination'] ?? null;
    $this->errors = $data['errors'] ?? [];
    $this->applySeoMetadata($view, $data);

    ob_start();
    include self::VIEWS_ROOT . "/$view.html.php";
    $this->content = ob_get_clean();
    return $this->content;
  }

  /**
   * Stores SEO-related metadata for the current view render cycle.
   *
   * @param string $view The view path being rendered.
   * @param array  $data Render payload that may include title/meta keys.
   */
  private function applySeoMetadata(string $view, array $data): void
  {
    $this->title = $this->resolveSeoTitle($data);
    $this->metaDescription = $this->resolveExplicitMetaDescription($data);
    $this->metaDescriptionSource = $this->resolveMetaDescriptionSource($view, $data);
  }

  /**
   * Resolves the page title used in SEO tags and <title>.
   *
   * @param array $data Render payload.
   * @return string A non-empty page title.
   */
  private function resolveSeoTitle(array $data): string
  {
    $title = trim((string) ($data['title'] ?? ''));
    return $title !== '' ? $title : t("app.default_title");
  }

  /**
   * Resolves an explicitly provided meta description, if any.
   *
   * @param array $data Render payload.
   * @return string|null Explicit meta description or null when absent.
   */
  private function resolveExplicitMetaDescription(array $data): ?string
  {
    $metaDescription = trim((string) ($data['meta_description'] ?? ''));
    return $metaDescription !== '' ? $metaDescription : null;
  }

  /**
   * Resolves the best source text for generated meta descriptions.
   *
   * @param string $view The view path being rendered.
   * @param array  $data Render payload.
   * @return string|null Description source text or null when unavailable.
   */
  private function resolveMetaDescriptionSource(string $view, array $data): ?string
  {
    $metaDescriptionSource = trim((string) ($data['meta_description_source'] ?? ''));
    if ($metaDescriptionSource !== '') {
      return $metaDescriptionSource;
    }
    if (str_ends_with($view, '/show')) {
      return $this->inferMetaDescriptionSource($data);
    }
    return null;
  }

  /**
   * Returns the currently resolved page title.
   *
   * @return string|null The current title or null if not resolved yet.
   */
  public function getTitle(): ?string
  {
    return $this->title ?? null;
  }

  /**
   * Returns an explicit page meta description, when set.
   *
   * @return string|null Explicit meta description value.
   */
  public function getMetaDescription(): ?string
  {
    return $this->metaDescription;
  }

  /**
   * Returns the source text used for generated meta descriptions.
   *
   * @return string|null Meta description source text.
   */
  public function getMetaDescriptionSource(): ?string
  {
    return $this->metaDescriptionSource;
  }

  /**
   * Indicates whether the current view is a detail/show page.
   *
   * @return bool True when current view path ends with /show.
   */
  public function isShowPage(): bool
  {
    return str_ends_with($this->view, '/show');
  }

  /**
   * Infers a fallback text source for show-page meta descriptions.
   *
   * @param array $data Render payload that may contain resource objects.
   * @return string|null First non-empty candidate value from known resource fields.
   */
  private function inferMetaDescriptionSource(array $data): ?string
  {
    $resourceKeys = ['post', 'event', 'voting', 'user'];
    $resourceFields = ['description', 'body', 'email'];

    foreach ($resourceKeys as $resourceKey) {
      $resource = $data[$resourceKey] ?? null;
      if (!is_object($resource)) {
        continue;
      }
      foreach ($resourceFields as $field) {
        $rawValue = trim((string) ($resource->{$field} ?? ''));
        if ($rawValue !== '') {
          return $rawValue;
        }
      }
    }

    return null;
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
    include self::VIEWS_ROOT . "/$filename.html.php";
    echo ob_get_clean();
    \Logger::debug("Rendered partial: app/views/$filename.html.php");
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
  public function renderTextarea(\ActiveModel $object, string $attribute, bool $required = true): string
  {
    $fieldId = toSnakeCase($object::class) . "-" . $attribute . "-input";
    $placeholder = t("attributes." . toSnakeCase($object::class) . "." . $attribute);
    $name = toSnakeCase($object::class) . "[" . $attribute . "]";
    $label = $object::humanAttributeName($attribute);
    $currentValue = (string) ($object->{$attribute} ?? '');
    $fluidLabelClass = trim('a11y-fluid-label ' . ($currentValue !== '' ? 'a11y-fluid-label--filled' : ''));
    $class = "";
    $hasError = false;
    $describedBy = '';

    if (isset($this->errors) && !empty($this->errors)) {
      foreach ($this->errors as $error) {
        if ($error['attribute'] === $attribute) {
          $class = "warning";
          $hasError = true;
          $describedBy = "aria-describedby='error-" . toSnakeCase($object::class) . "-" . $attribute . "'";
          break;
        }
      }
    }
    return "<label class='" . $fluidLabelClass . "' for='" . $fieldId . "'><span>" . $label . "</span>" .
      "<textarea id='" . $fieldId . "' class='" . $class . "' placeholder='" . $placeholder . "' name='" . $name . "' " . ($required ? "required" : "") . " " . ($hasError ? "aria-invalid='true'" : "") . " " . $describedBy . ">" . htmlspecialchars($currentValue) . "</textarea></label>";
  }

  /**
   * Renders an input form field for a given object's attribute, with optional validation error highlighting.
   * @param object $object The object whose attribute is being rendered (e.g. a Post, Voting, etc.)
   * @param string $attribute The name of the attribute to render (e.g. "name", "description", etc.)
   * @param string $type The type of the input field (e.g. "text", "email", "password", etc.). Default is "text".
   * @param bool $required Whether the field is required (default: true). If true, the "required" attribute will be added to the input.
   * @return string The HTML string for the input form field.
   */
  public function renderInput(\ActiveModel $object, string $attribute, string $type = "text", bool $required = true): string
  {
    $fieldId = toSnakeCase($object::class) . "-" . $attribute . "-input";
    $label = $object::humanAttributeName($attribute);
    $placeholder = t("attributes." . toSnakeCase($object::class) . "." . $attribute);
    $name = toSnakeCase($object::class) . "[" . $attribute . "]";
    $rawValue = (string) ($object->{$attribute} ?? '');
    $value = htmlspecialchars($rawValue);
    $fluidLabelClass = trim('a11y-fluid-label ' . ($rawValue !== '' ? 'a11y-fluid-label--filled' : ''));
    $class = "";
    $hasError = false;
    $describedBy = '';

    if (isset($this->errors) && !empty($this->errors)) {
      foreach ($this->errors as $error) {
        if ($error['attribute'] === $attribute) {
          $class = "warning";
          $hasError = true;
          $describedBy = "aria-describedby='error-" . toSnakeCase($object::class) . "-" . $attribute . "'";
          break;
        }
      }
    }
    return "<label class='" . $fluidLabelClass . "' for='" . $fieldId . "'><span>" . $label . "</span>" .
      "<input type='" . $type . "' id='" . $fieldId . "' class='" . $class . "' placeholder='" . $placeholder . "' name='" . $name . "' value='" . $value . "' " . ($required ? "required" : "") . " " . ($hasError ? "aria-invalid='true'" : "") . " " . $describedBy . " /></label>";
  }

  /**
   * Render a destroy button for a simple path
   * e.g. /posts/:id/destroy, /votings/:id/destroy, etc.
   * Only renders if the current user is the creator of the object
   * Uses CSRF token for protection
   * @param ActiveModel $object The object for which to render the destroy button
   * @return string The HTML for the destroy button, or an empty string if the user is not the creator of the object
   */
  public function renderDestroyButton(\ActiveModel $object): string
  {
    if ($object->creator_id == $this->auth->getUserId()) {
      $path = "/" . toSnakeCase($object::class) . "s/" . $object->id . "/destroy";
      return "<form action='" . $path . "' method='POST'>" .
        "<input type='hidden' name='token' value='" . hash_hmac('sha256', $path, $_SESSION['token']) . "' />" .
        "<input type='hidden' name='id' value='" . $object->id . "' />" .
        "<button class='button button--danger' type='submit'>" . $this->renderIcon('trash') . " " . t("delete") . "</button>" .
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
    include self::VIEWS_ROOT . '/layouts/forms/_csrf_token.html.php';
    echo ob_get_clean();
    return;
  }

  /**
   * Renders an inline SVG icon from the Heroicons library (https://heroicons.com).
   * Icon names match Heroicons originals, e.g.: arrow-right-on-rectangle, arrow-left-on-rectangle,
   * plus-circle, pencil-square, trash, x-mark, paper-clip, play, check-circle,
   * chat-bubble-left-ellipsis, chevron-left, chevron-right, user-plus, check.
   * @param string $icon_name The original Heroicons name of the icon to render.
   * @return string The inline SVG HTML string for the icon, or an empty string if the icon is not found.
   */
  public function renderIcon(string $icon_name, bool $decorative = true): string
  {
    $accessibilityAttrs = $decorative ? 'aria-hidden="true" focusable="false"' : 'role="img"';
    $attrs = 'xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18" ' . $accessibilityAttrs . ' style="margin-right: 4px; vertical-align: text-bottom;"';
    switch ($icon_name) {
      case 'arrow-right-on-rectangle':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>';
      case 'arrow-left-on-rectangle':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" /></svg>';
      case 'plus-circle':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>';
      case 'pencil-square':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>';
      case 'trash':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>';
      case 'x-mark':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>';
      case 'paper-clip':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>';
      case 'play':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>';
      case 'check-circle':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>';
      case 'chat-bubble-left-ellipsis':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>';
      case 'chevron-left':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>';
      case 'chevron-right':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>';
      case 'user-plus':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>';
      case 'check':
        return '<svg ' . $attrs . '><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>';
      default:
        return '';
    }
  }

  /**
   * The destructor method for the ViewManager class. This method is called when the object is destroyed,
   * which typically happens at the end of the request lifecycle.
   * It includes the main application layout file (app/views/layouts/application.html.php),
   * which will render the final HTML response to be sent to the client.
   * The layout file can access the $content property of the ViewManager to include the rendered view content
   * within the overall page layout.
   */
  public function __destruct()
  {
    if ($this->shouldRenderLayout) {
      include self::VIEWS_ROOT . '/layouts/application.html.php';
    }
  }

  /**
   * Disables layout rendering in __destruct(), useful for JSON endpoints.
   */
  public function disableLayout(): void
  {
    $this->shouldRenderLayout = false;
  }

  /**
   * Sends a JSON response and disables layout rendering.
   *
   * @param array $payload Data to encode as JSON.
   * @param int $statusCode HTTP status code (default 200).
   */
  public function renderJson(array $payload, int $statusCode = 200): void
  {
    $this->disableLayout();

    // Prevent buffered notices/warnings or partial HTML from preceding JSON.
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit();
  }
}
