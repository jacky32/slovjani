<?php

/**
 * Public read-only controller for published posts and detail pages.
 *
 * @package Controllers
 */
class PostsController extends ApplicationController
{
  private $id;

  /**
   * Parses the post ID from the request URI.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/posts\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  /**
   * Lists publicly visible (published) posts with pagination.
   *
   * @param array $request Parsed request data (expects 'page' key).
   * @return void
   */
  public function index($request)
  {
    $pagination = Post::publiclyVisible()->paginate($request['page']);
    $this->render("posts/index", [
      "posts" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  /**
   * Shows a single published post with its public attachments.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function show($request)
  {
    $post = Post::publiclyVisible()->find($this->id);
    if ($post) {
      $pagination = Post::publiclyVisible()->paginate($request['page'], $this->id);
      $attachments = $post->attachments->where(['is_publicly_visible' => true])->get();
      $this->render("posts/show", [
        "post" => $post,
        "posts" => $pagination->resources,
        "pagination" => $pagination,
        "attachments" => $attachments
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /posts");
    }
  }
}
