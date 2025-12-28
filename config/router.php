<?php
class Router
{
  public $controllerName;
  public $action;
  private $requestMethod;
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
    if (preg_match($regexp, $this->routeAction)) {
      return true;
    } else {
      return false;
    }
  }

  public function resources($resource, $actions = ["index", "show", "new", "create", "edit", "update", "destroy"])
  {
    $this->controllerName = ucfirst($resource) . 'Controller';
    $base = '/' . $resource;

    switch ($this->routeAction) {
      case $this->regRoute($base . '\/\d+\/destroy/'):
        if ($this->isPost() && in_array('destroy', $actions)) {
          $this->action = 'destroy';
          break;
        }
      case $this->regRoute($base . '\/\d+/'):
        if ($this->isGet() && (in_array('show', $actions))) {
          $this->action = 'show';
          break;
        }
        if ($this->isPost() && (in_array('update', $actions))) {
          $this->action = 'update';
          break;
        }
      case $base . '/new':
        if ($this->isGet() && in_array('new', $actions)) {
          $this->action = 'new';
          break;
        }
      case $this->regRoute($base . '\/\d+\/edit/'):
        if ($this->isGet() && in_array('edit', $actions)) {
          $this->action = 'edit';
          break;
        }
      case $base:
        if ($this->isGet() && in_array('index', $actions)) {
          $this->action = 'index';
          break;
        }
        if ($this->isPost() && in_array('create', $actions)) {
          $this->action = 'create';
          break;
        }
    }
    if (isset($this->action)) {
      return true;
    } else {
      return false;
    }
  }

  public function __construct()
  {
    $this->requestMethod = $_SERVER['REQUEST_METHOD'];
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
        switch ($this->requestMethod) {
          case 'GET':
            $this->action = 'new';
            break;
          case 'POST':
            $this->action = 'create';
            break;
        }
        break;
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
