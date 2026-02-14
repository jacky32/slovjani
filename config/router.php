<?php

/**
 * Simple Router class to map URLs to controller actions.
 * Supports RESTful resource routing for basic CRUD operations.
 */
class Router
{
  public $controllerName;
  public $action;
  private $routeAction;

  public function __construct()
  {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $this->routeAction = $this->normalizeTrailingSlash($path);

    // /posts
    if ($this->resources('posts')) {
      return;
    }

    // /posts/:id/attachments
    if ($this->nestedResources('posts', 'attachments', false, ["show"])) {
      return;
    }

    // /events
    if ($this->resources('events')) {
      return;
    }

    // /events/:id/attachments
    if ($this->nestedResources('events', 'attachments', false, ["show"])) {
      return;
    }

    // /admin/posts
    if ($this->resources('posts', true)) {
      return;
    }

    // /admin/posts/:id/attachments
    if ($this->nestedResources('posts', 'attachments', true, ["show", "new", "create", "edit", "update", "destroy"])) {
      return;
    }

    // /admin/events
    if ($this->resources('events', true)) {
      return;
    }

    // /admin/events/:id/attachments
    if ($this->nestedResources('events', 'attachments', true, ["show", "new", "create", "edit", "update", "destroy"])) {
      return;
    }

    // /admin/users
    if ($this->resources('users', true, ["index", "show", "edit", "update", "destroy"])) {
      return;
    }

    // /admin/users/:id/attachments
    if ($this->nestedResources('users', 'attachments', true, ["show", "new", "create", "edit", "update", "destroy"])) {
      return;
    }

    // /admin/votings
    if ($this->resources('votings', true)) {
      return;
    }

    // /admin/votings/:id/questions
    if ($this->nestedResources('votings', 'questions', true, ["new", "create", "edit", "update", "destroy"])) {
      return;
    }

    // /admin/votings/:id/users_questions
    if ($this->nestedResources('votings', 'users_questions', true, ["new", "create", "destroy"])) {
      return;
    }

    // /admin/votings/:id/attachments
    if ($this->nestedResources('votings', 'attachments', true, ["show", "new", "create", "edit", "update", "destroy"])) {
      return;
    }


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

  /**
   * isGet
   * Checks if the request method is GET
   *
   * @return bool
   */
  private function isGet()
  {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
  }

  /**
   * isPost
   * Checks if the request method is POST
   *
   * @return bool
   */
  private function isPost()
  {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
  }

  /**
   * regRoute
   * Checks if the current route matches a given regular expression
   *
   * @param string $regexp
   * @return bool
   */
  private function regRoute($regexp)
  {
    return (bool) preg_match($regexp, $this->routeAction);
  }

  /**
   * resourcePattern
   * Builds a regex pattern for resource routes
   *
   * @param string $base
   * @param string $suffix
   * @return string generated regex pattern
   */
  private function resourcePattern($base, $suffix = '')
  {
    return '/^' . preg_quote($base, '/') . $suffix . '$/';
  }

  /**
   * resources
   * Defines RESTful routes for a given resource and specified actions.
   *
   * @param string $resource Resource name (e.g., 'posts'). Must be plural.
   * @param array $actions Optional. List of actions to create routes for. Defaults to all CRUD actions.
   * @return bool True if a route is matched, false otherwise.
   */
  private function resources($resource, $admin = false, $actions = ["index", "show", "new", "create", "edit", "update", "destroy"])
  {
    $this->controllerName = ($admin ? 'Admin' : '') . toPascalCase($resource) . 'Controller';
    $base = '/' . ($admin ? 'admin/' : '') . $resource;

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

  /**
   * nestedResources
   * Defines nested RESTful routes for a child resource under a parent.
   * e.g., /admin/votings/:voting_id/questions
   *
   * @param string $parent Parent resource name (e.g., 'votings')
   * @param string $child Child resource name (e.g., 'questions')
   * @param bool $admin Whether to prefix with /admin
   * @param array $actions List of actions to create routes for
   * @return bool True if a route is matched, false otherwise.
   */
  private function nestedResources($parent, $child, $admin = false, $actions = ["index", "show", "new", "create", "edit", "update", "destroy"])
  {
    $this->controllerName = ($admin ? 'Admin' : '') . toPascalCase($child) . 'Controller';
    $prefix = $admin ? '\/admin\/' : '\/';
    $basePattern = '/^' . $prefix . $parent . '\/\d+\/' . $child;

    // POST /parent/:id/child/:id/destroy - destroy
    if ($this->regRoute($basePattern . '\/\d+\/destroy$/') && $this->isPost() && in_array('destroy', $actions)) {
      $this->action = 'destroy';
      return true;
    }

    // GET /parent/:id/child/:id/edit - edit
    if ($this->regRoute($basePattern . '\/\d+\/edit$/') && $this->isGet() && in_array('edit', $actions)) {
      $this->action = 'edit';
      return true;
    }

    // GET /parent/:id/child/:id - show
    if ($this->regRoute($basePattern . '\/\d+$/') && $this->isGet() && in_array('show', $actions)) {
      $this->action = 'show';
      return true;
    }

    // POST /parent/:id/child/:id - update
    if ($this->regRoute($basePattern . '\/\d+$/') && $this->isPost() && in_array('update', $actions)) {
      $this->action = 'update';
      return true;
    }

    // GET /parent/:id/child/new - new
    if ($this->regRoute($basePattern . '\/new$/') && $this->isGet() && in_array('new', $actions)) {
      $this->action = 'new';
      return true;
    }

    // GET /parent/:id/child - index
    if ($this->regRoute($basePattern . '$/') && $this->isGet() && in_array('index', $actions)) {
      $this->action = 'index';
      return true;
    }

    // POST /parent/:id/child - create
    if ($this->regRoute($basePattern . '$/') && $this->isPost() && in_array('create', $actions)) {
      $this->action = 'create';
      return true;
    }

    return false;
  }

  // Remove trailing slash (but keep root '/') so routes match consistently
  private function normalizeTrailingSlash($path)
  {
    $normalized = rtrim($path, '/');
    if ($normalized === '') {
      $normalized = '/';
    }
    return $normalized;
  }
}
