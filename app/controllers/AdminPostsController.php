<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;
use App\Services\AttachmentMarkupMediaSourceResolver;
use App\Services\EditorMarkupParser;
use App\Services\StaticPageGenerator;

/**
 * Admin CRUD controller for posts and static-page regeneration triggers.
 *
 * @package Controllers
 */
class AdminPostsController extends AdminController
{
  private $id;

  /**
   * Parses the post ID from the request URI.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/admin\/posts\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  /**
   * Lists all posts with pagination.
   *
   * @param array $request Parsed request data (expects 'page' key).
   * @return void
   */
  public function index($request)
  {
    $pagination = Post::paginate($request['page']);
    $this->render("admin/posts/index", [
      "posts" => $pagination->resources,
      "pagination" => $pagination,
    ]);
  }

  /**
   * Shows details for a single post.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function show($request)
  {
    $post = Post::find($this->id);
    $pagination = Post::paginate($request['page'], $this->id);
    if ($post) {
      $parsedBody = (new EditorMarkupParser(
        new AttachmentMarkupMediaSourceResolver(Post::class, $post->id, 'posts', true, true)
      ))->parse($post->body ?? '');
      $this->render("admin/posts/show", [
        "post" => $post,
        "posts" => $pagination->resources,
        "pagination" => $pagination,
        "parsed_body" => $parsedBody,
        "title" => (string) ($post->name ?? ''),
        "meta_description_source" => (string) ($post->body ?? ''),
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /admin/posts");
    }
  }

  /**
   * Renders the new-post creation form.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function new($request)
  {
    $pagination = Post::paginate($request['page'], $this->id);
    $this->render("admin/posts/new", [
      "post" => new Post(),
      "posts" => $pagination->resources,
      "pagination" => $pagination,
    ]);
  }

  /**
   * Persists a new post and regenerates static pages.
   *
   * @param array $request Parsed request data including post attributes.
   * @return void
   */
  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/posts');

      // Create new post
      $post = new Post([
        'name' => $request['post']['name'],
        'body' => $request['post']['body'],
        'status' => $request['post']['status'],
        'creator_id' => $this->auth->getUserId()
      ]);
      $post->save();
      (new StaticPageGenerator())->regenerateAll();
      $this->addFlash('success', t("posts.create.success"));
      header("Location: /admin/posts");
    } catch (\Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = Post::paginate($request['page']);
      $this->render("admin/posts/new", [
        "posts" => $pagination->resources,
        "post" => new Post([
          'name' => $request['post']['name'],
          'body' => $request['post']['body'],
          'status' => $request['post']['status']
        ]),
        "errors" => $errors,
        "pagination" => $pagination,
      ]);
    }
  }

  /**
   * Renders the edit form for an existing post.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function edit($request)
  {
    $post = Post::find($this->id);
    $pagination = Post::paginate($request['page'], $this->id);
    if ($post) {
      $this->render("admin/posts/edit", [
        "post" => $post,
        "posts" => $pagination->resources,
        "pagination" => $pagination,
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /admin/posts");
    }
  }

  /**
   * Updates an existing post and conditionally regenerates static pages.
   *
   * @param array $request Parsed request data including updated post attributes.
   * @return void
   */
  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/posts/' . $this->id);

      // Find post
      $post = Post::find($this->id);
      if ($post) {
        $oldStatus = $post->status;
        $post->name = $request['post']['name'];
        $post->body = $request['post']['body'];
        $post->status = $request['post']['status'];
        $post->save();
        if ($oldStatus === 'PUBLISHED' || $post->status === 'PUBLISHED') {
          (new StaticPageGenerator())->regenerateAll();
        }
        $this->addFlash('success', t("posts.update.success"));
        header("Location: /admin/posts/" . $post->id);
      } else {
        if (!$post) {
          $this->addFlash('error', t("posts.show.post_not_found"));
        }
        header("Location: /admin/posts/" . $this->id . "/edit");
      }
    } catch (\Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = Post::paginate($request['page'], $this->id);
      $this->render("admin/posts/edit", [
        "post" => $post,
        "posts" => $pagination->resources,
        "pagination" => $pagination,
        "errors" => $errors,
      ]);
    }
  }

  /**
   * Deletes a post and triggers static page regeneration if it was published.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/posts/' . $this->id . '/destroy');

      // Find post
      $post = Post::find($this->id);
      if ($post) {
        $wasPublished = $post->status === 'PUBLISHED';
        $post->destroy();
        if ($wasPublished) {
          (new StaticPageGenerator())->regenerateAll();
        }
        $this->addFlash('success', t("posts.destroy.success"));
      } else {
        if (!$post) {
          $this->addFlash('error', t("posts.destroy.not_found"));
        }
        $this->addFlash('error', t("error"));
      }
      header("Location: /admin/posts");
    } catch (\Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/posts/" . $this->id);
    }
  }
}
