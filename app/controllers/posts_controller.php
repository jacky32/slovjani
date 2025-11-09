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

  public function create($request)
  {
    try {
      // if (isset($request['body'])) {
      // }
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
    $post = Post::find($request['id']);
    if ($post && $post->get_author_id() == $this->auth->getUserId()) {
      $post->destroy();
      $this->addFlash('success', "Příspěvek byl úspěšně smazán.");
    } else {
      if (!$post) {
        $this->addFlash('error', "Příspěvek neexistuje.");
      } else if ($post->get_author_id() != $this->auth->getUserId()) {
        $this->addFlash('error', "Nemáte oprávnění smazat tento příspěvek.");
      }
      $this->addFlash('error', "Nastala chyba");
    }
    header("Location: /posts");
  }
}
