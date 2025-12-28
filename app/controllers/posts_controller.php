<?php
class PostsController extends ApplicationController
{
  private $post;

  public function __construct($postModel)
  {
    parent::__construct();
    $this->post = $postModel;
  }


  public function index($request)
  {
    $this->render("posts/index", [
      "posts" => Post::all()
    ]);
  }

  public function show($request)
  {
    $post = $this->findPostById();
    if ($post) {
      $this->render("posts/show", [
        "post" => $post,
        "posts" => Post::all(),
        "id" => $post->id
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /posts");
    }
  }

  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/posts');
      // Create new post
      $post = new Post([
        'name' => $request['name'],
        'body' => $request['body'],
        'author_id' => $this->auth->getUserId()
      ]);
      $post->save();
      $this->addFlash('success', "Příspěvek byl úspěšně vytvořen.");
      header("Location: /posts");
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
      $this->render("posts/index", [
        "posts" => Post::all(),
        "errors" => $errors,
      ]);
    }
  }

  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/posts/destroy');

      // Find post and check ownership
      $post = $this->findPostById();
      if ($post && $post->author_id == $this->auth->getUserId()) {
        $post->destroy();
        $this->addFlash('success', "Příspěvek byl úspěšně smazán.");
      } else {
        if (!$post) {
          $this->addFlash('error', "Příspěvek neexistuje.");
        } else if ($post->author_id != $this->auth->getUserId()) {
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

  private function findPostById()
  {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    preg_match('/posts\/\d+/', $uri, $matches);
    $id = explode('/', $matches[0])[1];
    return Post::find($id);
  }
}
