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
      "posts" => Post::all() // TODO: Pagination
    ]);
  }

  public function show($request)
  {
    $post = Post::find($this->id);
    if ($post) {
      $this->render("posts/show", [
        "post" => $post,
        "posts" => Post::all()
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /posts");
    }
  }

  public function new($request)
  {
    $this->render("posts/new", [
      "posts" => Post::all()
    ]);
  }

  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/posts');

      // Create new post
      $post = new Post([
        'name' => $request['post']['name'],
        'body' => $request['post']['body'],
        'status' => $request['post']['status'],
        'creator_id' => $this->auth->getUserId()
      ]);
      $post->save();
      $this->addFlash('success', "Příspěvek byl úspěšně vytvořen.");
      header("Location: /posts");
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
      if ($e instanceof \ActiveModel\ValidationException) {
        $this->addFlash('error', $e->getMessage());
      }
      $this->render("posts/new", [
        "posts" => Post::all(),
        "errors" => $errors,
      ]);
    }
  }

  public function edit($request)
  {
    $post = Post::find($this->id);
    if ($post) {
      $this->render("posts/edit", [
        "post" => $post,
        "posts" => Post::all()
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /posts");
    }
  }

  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/posts/' . $this->id);

      // Find post and check ownership
      $post = Post::find($this->id);
      if ($post && $post->creator_id == $this->auth->getUserId()) {
        $post->name = $request['post']['name'];
        $post->body = $request['post']['body'];
        $post->status = $request['post']['status'];
        $post->save();
        $this->addFlash('success', t("posts.update.success"));
        header("Location: /posts/" . $post->id);
      } else {
        if (!$post) {
          $this->addFlash('error', t("posts.show.post_not_found"));
        } else if ($post->creator_id != $this->auth->getUserId()) { // TODO: Authorization check - move to users role
          $this->addFlash('error', t("posts.update.unauthorized"));
        }
        header("Location: /posts/" . $post->id . "/edit");
      }
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
      if ($e instanceof \ActiveModel\ValidationException) {
        $this->addFlash('error', $e->getMessage());
      }
      $this->render("posts/edit", [
        "post" => $post,
        "posts" => Post::all(),
        "errors" => $errors,
      ]);
    }
  }

  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/posts/' . $this->id . '/destroy');

      // Find post and check ownership
      $post = Post::find($this->id);
      if ($post && $post->creator_id == $this->auth->getUserId()) {
        $post->destroy();
        $this->addFlash('success', "Příspěvek byl úspěšně smazán.");
      } else {
        if (!$post) {
          $this->addFlash('error', "Příspěvek neexistuje.");
        } else if ($post->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', "Nemáte oprávnění smazat tento příspěvek.");
        }
        $this->addFlash('error', "Nastala chyba");
      }
      header("Location: /posts");
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /posts");
    }
  }
}
