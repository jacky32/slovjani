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
    $pagination = Post::paginate($request['page']);
    $this->render("admin/posts/index", [
      "posts" => $pagination->resources,
      "pagination" => $pagination,
    ]);
  }

  public function show($request)
  {
    $post = Post::find($this->id);
    $pagination = Post::paginate($request['page'], $this->id);
    if ($post) {
      $this->render("admin/posts/show", [
        "post" => $post,
        "posts" => $pagination->resources,
        "pagination" => $pagination,
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /admin/posts");
    }
  }

  public function new($request)
  {
    $pagination = Post::paginate($request['page'], $this->id);
    $this->render("admin/posts/new", [
      "posts" => Post::all(),
      "pagination" => $pagination,
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
      $pagination = Post::paginate($request['page'], $this->id);
      $this->render("admin/posts/edit", [
        "post" => $post,
        "posts" => $pagination->resources,
        "pagination" => $pagination,
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
