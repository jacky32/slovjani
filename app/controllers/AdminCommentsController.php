<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Comment;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use App\Models\Voting;

/**
 * Admin controller for managing comments on posts, users, events, and votings.
 *
 * @package Controllers
 */
class AdminCommentsController extends AdminController
{
  private $resource_type;
  private $resource_id;
  private $comment_id;
  private $resource;

  /**
   * Parses the resource type, resource ID, and optional comment ID from the
   * request URI and locates the parent resource.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/\/admin\/(posts|users|events|votings)\/(\d+)\/comments(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->resource_type = $matches[1] ?? null;
    $this->resource_id = $matches[2] ?? null;
    $this->comment_id = $matches[3] ?? null;
    $this->resource = $this->findResource();
  }

  /**
   * Persists a new comment on the parent resource.
   *
   * @param array $request Parsed request data including comment body and optional parent ID.
   * @return void
   */
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
    } catch (\Exception $e) {
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

  /**
   * Updates the body of an existing comment owned by the current user.
   *
   * @param array $request Parsed request data including updated comment body.
   * @return void
   */
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
    } catch (\Exception $e) {
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

  /**
   * Deletes a comment owned by the current user.
   *
   * @param array $request Parsed request data.
   * @return void
   */
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
    } catch (\Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
    }
  }

  /**
   * Finds the comment by ID and verifies it belongs to the current resource.
   *
   * @return Comment|null The matching Comment, or null if not found / mismatched resource.
   */
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

  /**
   * Resolves the parent resource instance from the parsed resource type and ID.
   *
   * @return Post|User|Event|Voting|null The found resource, or null for unknown types.
   */
  private function findResource(): Post|User|Event|Voting|null
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

