<?php
class PostsController extends ApplicationController
{
  private $id;

  public function __construct()
  {
    parent::__construct();

    preg_match('/posts\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  public function index($request)
  {
    $this->render("posts/index", [
      "posts" => Post::publiclyVisible()->orderBy('created_at', 'desc')->get() // TODO: Pagination
    ]);
  }

  public function show($request)
  {
    $post = Post::publiclyVisible()->find($this->id);
    if ($post) {
      $this->render("posts/show", [
        "post" => $post,
        "posts" => Post::publiclyVisible()->orderBy('created_at', 'desc')->get() // TODO: Pagination
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /posts");
    }
  }
}
