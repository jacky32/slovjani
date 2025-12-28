<?php
class Router
{
  public $controllerName;
  public $action;
  private $routeAction;

  private function isGet()
  {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
  }

  private function isPost()
  {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
  }
  public function regRoute($regexp)
  {
    return (bool) preg_match($regexp, $this->routeAction);
  }

  private function resourcePattern($base, $suffix = '')
  {
    return '/^' . preg_quote($base, '/') . $suffix . '$/';
  }

  public function resources($resource, $actions = ["index", "show", "new", "create", "edit", "update", "destroy"])
  {
    $this->controllerName = ucfirst($resource) . 'Controller';
    $base = '/' . $resource;

    // POST /resource/:id/destroy - destroy
    if ($this->regRoute($this->resourcePattern($base, '\/\d+\/destroy')) && $this->isPost() && in_array('destroy', $actions)) {
      $this->action = 'destroy';
      return true;
    }

    // GET /resource/:id/edit - edit
    if ($this->regRoute($this->resourcePattern($base, '\/\d+\/edit')) && $this->isGet() && in_array('edit', $actions)) {
      $this->action = 'edit';
      return true;
    }

    // GET /resource/:id - show
    if ($this->regRoute($this->resourcePattern($base, '\/\d+')) && $this->isGet() && in_array('show', $actions)) {
      $this->action = 'show';
      return true;
    }

    // POST /resource/:id - update
    if ($this->regRoute($this->resourcePattern($base, '\/\d+')) && $this->isPost() && in_array('update', $actions)) {
      $this->action = 'update';
      return true;
    }

    // GET /resource/new - new
    if ($this->routeAction === $base . '/new' && $this->isGet() && in_array('new', $actions)) {
      $this->action = 'new';
      return true;
    }

    // GET /resource - index
    if ($this->routeAction === $base && $this->isGet() && in_array('index', $actions)) {
      $this->action = 'index';
      return true;
    }

    // POST /resource - create
    if ($this->routeAction === $base && $this->isPost() && in_array('create', $actions)) {
      $this->action = 'create';
      return true;
    }

    return false;
  }

  public function __construct()
  {
    $this->routeAction = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($this->resources('posts')) {
      return;
    }

    // OPTIMIZE: pattern matching in php ???
    switch ($this->routeAction) {
      case '/login':
        $this->controllerName = 'SessionsController';
        $this->action = $this->isGet() ? 'new' : 'create';
        break;
      case '/logout':
        $this->controllerName = 'SessionsController';
        if ($this->isPost()) {
          $this->action = 'destroy';
          break;
        }
      case '/registration':
        $this->controllerName = 'UsersController';
        if ($this->isGet()) {
          $this->action = 'new';
          break;
        }
        if ($this->isPost()) {
          $this->action = 'create';
          break;
        }
      case '/':
        $this->controllerName = 'HomeController';
        $this->action = 'index';
        break;
      default:
        $this->controllerName = 'ErrorsController';
        $this->action = 'notFound';
        break;
    }
    // echo "Routing to " . $this->controllerName . "->" . $this->action . "<br>";
  }
}
