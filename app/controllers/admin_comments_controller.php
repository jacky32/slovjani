<?php

/**
 * @package Controllers
 */
class AdminCommentsController extends AdminController
{
  private $resource_type;
  private $resource_id;
  private $comment_id;
  private $resource;

  public function __construct()
  {
    parent::__construct();

    preg_match('/\/admin\/(posts|users|events|votings)\/(\d+)\/comments(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->resource_type = $matches[1] ?? null;
    $this->resource_id = $matches[2] ?? null;
    $this->comment_id = $matches[3] ?? null;
    $this->resource = $this->findResource();
  }

  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF("/admin/{$this->resource_type}/{$this->resource_id}/comments");

      $comment = new Comment([
        'resource_id' => $this->resource_id,
        'resource_type' => $this->resource::class,
        'body' => $request['comment']['body'],
        'parent_comment_id' => $request['comment']['parent_comment_id'] ?? null,
        'creator_id' => $this->auth->getUserId()
      ]);
      $comment->save();
      $this->addFlash('success', t("comments.create.success"));
      header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $this->render("admin/comments/new", [
        "comment" => new Comment([
          'body' => $request['comment']['body'],
          'parent_comment_id' => $request['comment']['parent_comment_id'] ?? null,
        ]),
        "resource" => $this->resource,
        "resource_type" => $this->resource_type,
        "resource_id" => $this->resource_id,
        "errors" => $errors,
      ]);
    }
  }

  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF("/admin/{$this->resource_type}/{$this->resource_id}/comments/{$this->comment_id}");

      $comment = $this->findComment();
      if ($comment && $comment->creator_id == $this->auth->getUserId()) {
        $comment->body = $request['comment']['body'];
        $comment->save();
        $this->addFlash('success', t("comments.update.success"));
        header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
      } else {
        if (!$comment) {
          $this->addFlash('error', t("comments.show.comment_not_found"));
        } else if ($comment->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', t("comments.update.unauthorized"));
        }
        header("Location: /admin/{$this->resource_type}/{$this->resource_id}/comments/{$this->comment_id}/edit");
      }
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $this->render("admin/comments/edit", [
        "comment" => $comment,
        "resource" => $this->resource,
        "resource_type" => $this->resource_type,
        "resource_id" => $this->resource_id,
        "errors" => $errors,
      ]);
    }
  }

  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF("/admin/{$this->resource_type}/{$this->resource_id}/comments/{$this->comment_id}/destroy");

      $comment = $this->findComment();
      if ($comment && $comment->creator_id == $this->auth->getUserId()) {
        $comment->destroy();
        $this->addFlash('success', t("comments.destroy.success"));
      } else {
        if (!$comment) {
          $this->addFlash('error', t("comments.show.comment_not_found"));
        } else if ($comment->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', t("comments.destroy.unauthorized"));
        }
        $this->addFlash('error', t("comments.destroy.error"));
      }
      header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
    }
  }

  private function findComment()
  {
    $comment = Comment::find($this->comment_id);
    if (
      $comment &&
      $comment->resource_id == $this->resource_id &&
      $comment->resource_type == $this->resource::class
    ) {
      return $comment;
    }
    return null;
  }

  private function findResource()
  {
    switch ($this->resource_type) {
      case 'posts':
        return Post::find($this->resource_id);
      case 'users':
        return User::find($this->resource_id);
      case 'events':
        return Event::find($this->resource_id);
      case 'votings':
        return Voting::find($this->resource_id);
      default:
        return null;
    }
  }
}
