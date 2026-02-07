<?php
class AdminPostsController extends AdminController
{
  private $id;

  public function __construct()
  {
    parent::__construct();

    preg_match('/admin\/posts\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  public function index($request)
  {
    $this->render("admin/posts/index", [
      "posts" => Post::all() // TODO: Pagination
    ]);
  }

  public function show($request)
  {
    $post = Post::find($this->id);
    if ($post) {
      $this->render("admin/posts/show", [
        "post" => $post,
        "posts" => Post::all()
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /admin/posts");
    }
  }

  public function new($request)
  {
    $this->render("admin/posts/new", [
      "posts" => Post::all()
    ]);
  }

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
      $this->addFlash('success', "Příspěvek byl úspěšně vytvořen.");
      header("Location: /admin/posts");
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $this->render("admin/posts/new", [
        "posts" => Post::all(),
        "post" => new Post([
          'name' => $request['post']['name'],
          'body' => $request['post']['body'],
          'status' => $request['post']['status']
        ]),
        "errors" => $errors,
      ]);
    }
  }

  public function edit($request)
  {
    $post = Post::find($this->id);
    if ($post) {
      $this->render("admin/posts/edit", [
        "post" => $post,
        "posts" => Post::all()
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /admin/posts");
    }
  }

  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/posts/' . $this->id);

      // Find post and check ownership
      $post = Post::find($this->id);
      if ($post && $post->creator_id == $this->auth->getUserId()) {
        $post->name = $request['post']['name'];
        $post->body = $request['post']['body'];
        $post->status = $request['post']['status'];
        $post->save();
        $this->addFlash('success', t("posts.update.success"));
        header("Location: /admin/posts/" . $post->id);
      } else {
        if (!$post) {
          $this->addFlash('error', t("posts.show.post_not_found"));
        } else if ($post->creator_id != $this->auth->getUserId()) { // TODO: Authorization check - move to users role
          $this->addFlash('error', t("posts.update.unauthorized"));
        }
        header("Location: /admin/posts/" . $post->id . "/edit");
      }
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $this->render("admin/posts/edit", [
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
      $this->verifyCSRF('/admin/posts/' . $this->id . '/destroy');

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
      header("Location: /admin/posts");
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/posts/" . $this->id);
    }
  }
}
