<?php

declare(strict_types=1);

// Router depends on toPascalCase() from helpers.php.
if (!function_exists('toSnakeCase')) {
  require __DIR__ . '/../../lib/Helpers.php';
}
require_once __DIR__ . '/../../config/Router.php';

use PHPUnit\Framework\TestCase;

/**
 * Tests for Router.
 *
 * Routes are exercised by presetting $_SERVER superglobals and constructing a
 * new Router instance.  The test restores the original $_SERVER after every run.
 *
 * Coverage:
 *   - Public resource routes (GET / POST on posts, events)
 *   - Admin resource routes
 *   - Nested public and admin resource routes
 *   - Special switch-statement routes (/login, /logout, /registration, /)
 *   - Trailing-slash normalisation
 *   - Unknown path → ErrorsController::notFound
 */
final class RouterTest extends TestCase
{
  private array $originalServer;

  protected function setUp(): void
  {
    $this->originalServer = $_SERVER;
  }

  protected function tearDown(): void
  {
    $_SERVER = $this->originalServer;
  }

  /** Construct a Router after faking the HTTP environment. */
  private function route(string $method, string $uri): Router
  {
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI']    = $uri;
    return new Router();
  }

  // ---- Root / special routes ----

  public function testGetRootRoutesToHomeIndex(): void
  {
    $r = $this->route('GET', '/');
    $this->assertSame('HomeController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  public function testGetLoginRoutesToSessionsNew(): void
  {
    $r = $this->route('GET', '/login');
    $this->assertSame('SessionsController', $r->controllerName);
    $this->assertSame('new', $r->action);
  }

  public function testPostLoginRoutesToSessionsCreate(): void
  {
    $r = $this->route('POST', '/login');
    $this->assertSame('SessionsController', $r->controllerName);
    $this->assertSame('create', $r->action);
  }

  public function testPostLogoutRoutesToSessionsDestroy(): void
  {
    $r = $this->route('POST', '/logout');
    $this->assertSame('SessionsController', $r->controllerName);
    $this->assertSame('destroy', $r->action);
  }

  // ---- Posts (public) ----

  public function testGetPostsRoutesToPostsIndex(): void
  {
    $r = $this->route('GET', '/posts');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  public function testPostPostsRoutesToPostsCreate(): void
  {
    $r = $this->route('POST', '/posts');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('create', $r->action);
  }

  public function testGetPostsNewRoutesToPostsNew(): void
  {
    $r = $this->route('GET', '/posts/new');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('new', $r->action);
  }

  public function testGetPostsShowRoutesToPostsShow(): void
  {
    $r = $this->route('GET', '/posts/1');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('show', $r->action);
  }

  public function testPostPostsUpdateRoutesToPostsUpdate(): void
  {
    $r = $this->route('POST', '/posts/42');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('update', $r->action);
  }

  public function testGetPostsEditRoutesToPostsEdit(): void
  {
    $r = $this->route('GET', '/posts/7/edit');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('edit', $r->action);
  }

  public function testPostPostsDestroyRoutesToPostsDestroy(): void
  {
    $r = $this->route('POST', '/posts/3/destroy');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('destroy', $r->action);
  }

  // ---- Events (public) ----

  public function testGetEventsRoutesToEventsIndex(): void
  {
    $r = $this->route('GET', '/events');
    $this->assertSame('EventsController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  public function testGetEventsShowRoutesToEventsShow(): void
  {
    $r = $this->route('GET', '/events/5');
    $this->assertSame('EventsController', $r->controllerName);
    $this->assertSame('show', $r->action);
  }

  // ---- Nested public resource: post attachments ----

  public function testGetPostAttachmentShowRoutesToAttachmentsShow(): void
  {
    // /posts/:id/attachments/:id — only 'show' is registered for public
    $r = $this->route('GET', '/posts/5/attachments/3');
    $this->assertSame('AttachmentsController', $r->controllerName);
    $this->assertSame('show', $r->action);
  }

  public function testGetEventAttachmentShowRoutesToAttachmentsShow(): void
  {
    $r = $this->route('GET', '/events/2/attachments/1');
    $this->assertSame('AttachmentsController', $r->controllerName);
    $this->assertSame('show', $r->action);
  }

  // ---- Admin posts ----

  public function testGetAdminPostsRoutesToAdminPostsIndex(): void
  {
    $r = $this->route('GET', '/admin/posts');
    $this->assertSame('AdminPostsController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  public function testGetAdminPostsShowRoutesToAdminPostsShow(): void
  {
    $r = $this->route('GET', '/admin/posts/10');
    $this->assertSame('AdminPostsController', $r->controllerName);
    $this->assertSame('show', $r->action);
  }

  public function testGetAdminPostsEditRoutesToAdminPostsEdit(): void
  {
    $r = $this->route('GET', '/admin/posts/10/edit');
    $this->assertSame('AdminPostsController', $r->controllerName);
    $this->assertSame('edit', $r->action);
  }

  public function testPostAdminPostsCreateRoutesToAdminPostsCreate(): void
  {
    $r = $this->route('POST', '/admin/posts');
    $this->assertSame('AdminPostsController', $r->controllerName);
    $this->assertSame('create', $r->action);
  }

  public function testPostAdminPreviewsPreviewMarkupRoutesToAdminPreviewsPreviewMarkup(): void
  {
    $r = $this->route('POST', '/admin/previews/preview_markup');
    $this->assertSame('AdminPreviewsController', $r->controllerName);
    $this->assertSame('preview_markup', $r->action);
  }

  public function testPostAdminPostsDestroyRoutesToAdminPostsDestroy(): void
  {
    $r = $this->route('POST', '/admin/posts/10/destroy');
    $this->assertSame('AdminPostsController', $r->controllerName);
    $this->assertSame('destroy', $r->action);
  }

  // ---- Admin events ----

  public function testGetAdminEventsRoutesToAdminEventsIndex(): void
  {
    $r = $this->route('GET', '/admin/events');
    $this->assertSame('AdminEventsController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  // ---- Admin users ----

  public function testGetAdminUsersRoutesToAdminUsersIndex(): void
  {
    $r = $this->route('GET', '/admin/users');
    $this->assertSame('AdminUsersController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  public function testGetAdminUsersShowRoutesToAdminUsersShow(): void
  {
    $r = $this->route('GET', '/admin/users/1');
    $this->assertSame('AdminUsersController', $r->controllerName);
    $this->assertSame('show', $r->action);
  }

  public function testGetAdminUsersEditRoutesToAdminUsersEdit(): void
  {
    $r = $this->route('GET', '/admin/users/1/edit');
    $this->assertSame('AdminUsersController', $r->controllerName);
    $this->assertSame('edit', $r->action);
  }

  // ---- Admin votings ----

  public function testGetAdminVotingsRoutesToAdminVotingsIndex(): void
  {
    $r = $this->route('GET', '/admin/votings');
    $this->assertSame('AdminVotingsController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  public function testGetAdminVotingsShowRoutesToAdminVotingsShow(): void
  {
    $r = $this->route('GET', '/admin/votings/1');
    $this->assertSame('AdminVotingsController', $r->controllerName);
    $this->assertSame('show', $r->action);
  }

  // ---- Admin nested: votings → questions ----

  public function testGetAdminVotingQuestionsNewRoutesToAdminQuestionsNew(): void
  {
    $r = $this->route('GET', '/admin/votings/1/questions/new');
    $this->assertSame('AdminQuestionsController', $r->controllerName);
    $this->assertSame('new', $r->action);
  }

  public function testPostAdminVotingQuestionsCreateRoutesToAdminQuestionsCreate(): void
  {
    $r = $this->route('POST', '/admin/votings/1/questions');
    $this->assertSame('AdminQuestionsController', $r->controllerName);
    $this->assertSame('create', $r->action);
  }

  public function testPostAdminVotingQuestionsDestroyRoutesToAdminQuestionsDestroy(): void
  {
    $r = $this->route('POST', '/admin/votings/1/questions/3/destroy');
    $this->assertSame('AdminQuestionsController', $r->controllerName);
    $this->assertSame('destroy', $r->action);
  }

  // ---- Admin nested: votings → users_questions ----

  public function testGetAdminVotingUsersQuestionsNewRoutesToAdminUsersQuestionsNew(): void
  {
    $r = $this->route('GET', '/admin/votings/1/users_questions/new');
    $this->assertSame('AdminUsersQuestionsController', $r->controllerName);
    $this->assertSame('new', $r->action);
  }

  // ---- Admin nested: posts → attachments ----

  public function testGetAdminPostAttachmentEditRoutesToAdminAttachmentsEdit(): void
  {
    $r = $this->route('GET', '/admin/posts/1/attachments/2/edit');
    $this->assertSame('AdminAttachmentsController', $r->controllerName);
    $this->assertSame('edit', $r->action);
  }

  // ---- Admin nested: posts → comments ----

  public function testPostAdminPostCommentsCreateRoutesToAdminCommentsCreate(): void
  {
    $r = $this->route('POST', '/admin/posts/1/comments');
    $this->assertSame('AdminCommentsController', $r->controllerName);
    $this->assertSame('create', $r->action);
  }

  public function testPostAdminPostCommentsDestroyRoutesToAdminCommentsDestroy(): void
  {
    $r = $this->route('POST', '/admin/posts/1/comments/5/destroy');
    $this->assertSame('AdminCommentsController', $r->controllerName);
    $this->assertSame('destroy', $r->action);
  }

  // ---- Trailing-slash normalisation ----

  public function testTrailingSlashNormalisedForPostsIndex(): void
  {
    $r = $this->route('GET', '/posts/');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  public function testTrailingSlashNormalisedForRoot(): void
  {
    $r = $this->route('GET', '//');
    // '//' normalises to '/' → HomeController::index
    $this->assertSame('HomeController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }

  // ---- Unknown route ----

  public function testUnknownPathRoutesToErrorsNotFound(): void
  {
    $r = $this->route('GET', '/this/does/not/exist');
    $this->assertSame('ErrorsController', $r->controllerName);
    $this->assertSame('notFound', $r->action);
  }

  public function testUnknownAdminPathRoutesToErrorsNotFound(): void
  {
    $r = $this->route('GET', '/admin/unknown_resource');
    $this->assertSame('ErrorsController', $r->controllerName);
    $this->assertSame('notFound', $r->action);
  }

  // ---- Query string is stripped ----

  public function testQueryStringIsIgnoredForRouteMatching(): void
  {
    $r = $this->route('GET', '/posts?page=2');
    $this->assertSame('PostsController', $r->controllerName);
    $this->assertSame('index', $r->action);
  }
}
