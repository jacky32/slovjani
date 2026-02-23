<?php
/**
 * @package Controllers
 */
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
    $pagination = Post::publiclyVisible()->paginate($request['page']);
    $this->render("posts/index", [
      "posts" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

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
