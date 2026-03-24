<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Attachment;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use App\Models\Voting;

/**
 * Admin controller for creating, serving, and deleting resource attachments.
 *
 * @package Controllers
 */
class AdminAttachmentsController extends AdminController
{
  private $resource_type;
  private $resource_id;
  private $attachment_id;
  private $resource;

  /**
   * Parses the resource type, resource ID, and optional attachment ID from the
   * request URI and locates the parent resource.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/\/admin\/(posts|users|events|votings)\/(\d+)\/attachments(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->resource_type = $matches[1] ?? null;
    $this->resource_id = $matches[2] ?? null;
    $this->attachment_id = $matches[3] ?? null;
    $this->resource = $this->findResource();
  }

  /**
   * Streams the raw attachment file to the browser.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function show($request)
  {
    $attachment = $this->resource->attachments->find($this->attachment_id);
    if ($attachment) {
      header("Content-Type: " . $attachment->file_type);
      readfile(getcwd() . '/uploads/' . $attachment->token . '/' . $attachment->file_name);
    } else {
      $this->addFlash('error', t("attachments.show.attachment_not_found"));
      header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
    }
  }

  /**
   * Renders the upload form for a new attachment on the parent resource.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function new($request)
  {
    $pagination = $this->resource::class::paginate($request['page'], $this->resource_id);
    $this->render("admin/attachments/new", [
      "resource" => $this->resource,
      "resources" => $pagination->resources,
      "pagination" => $pagination,
      "resource_type" => $this->resource_type,
      "resource_id" => $this->resource_id,
    ]);
  }

  /**
   * Handles the multipart file upload, persists the Attachment record and
   * moves the file to the uploads directory.
   *
   * @param array $request Parsed request data including $_FILES.
   * @return void
   */
  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF("/admin/{$this->resource_type}/{$this->resource_id}/attachments");

      if ($_FILES['attachment']['error'][0] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['attachment']['tmp_name'][0];
        $fileSize = $_FILES['attachment']['size'][0];
        $fileType = $_FILES['attachment']['type'][0];
        $fileName = $_FILES['attachment']['name'][0];

        $attachment = new Attachment([
          'resource_id' => $this->resource_id,
          'resource_type' => $this->resource::class,
          'file_name' => $fileName,
          'file_size' => $fileSize,
          'file_type' => $fileType,
          'visible_name' => $request['attachment']['visible_name'] ?? $fileName,
          'is_publicly_visible' => isset($request['attachment']['is_publicly_visible']) ? true : 0,
          'creator_id' => $this->auth->getUserId()
        ]);
        $attachment->save();

        $uploadDir = getcwd() . '/uploads/' . $attachment->token;
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . '/' . basename($fileName);
        if (move_uploaded_file($fileTmpPath, $destination)) {
          // File moved successfully
        } else {
          $attachment->destroy(); // Rollback attachment record if file move fails
          throw new \Exception(t("attachments.create.file_move_error"));
        }
      } else {
        throw new \Exception("Error uploading file: " . $_FILES['attachment']['error'][0]);
      }
      $attachment->save();
      $this->addFlash('success', t("attachments.create.success"));
      header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
    } catch (\Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = $this->resource::class::paginate($request['page'], $this->resource_id);
      $this->render("admin/{$this->resource_type}/{$this->resource_id}/attachments/new", [
        "{$this->resource_type}" => $pagination->resources,
        "{$this->resource_type}" => new $this->resource([]),
        "errors" => $errors,
        "pagination" => $pagination,
      ]);
    }
  }

  /**
   * Deletes an attachment record and its corresponding file on disk.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF("/admin/{$this->resource_type}/{$this->resource_id}/attachments/{$this->attachment_id}/destroy");

      // Find post and check ownership
      $attachment = $this->resource->attachments->find($this->attachment_id);
      if ($attachment && $attachment->creator_id == $this->auth->getUserId()) {
        $attachment->destroy();
        $this->addFlash('success', t("attachments.destroy.success"));
      } else {
        if (!$attachment) {
          $this->addFlash('error', t("attachments.show.attachment_not_found"));
        } else if ($attachment->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', t("attachments.destroy.unauthorized"));
        }
        $this->addFlash('error', t("attachments.destroy.error"));
      }
      header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
    } catch (\Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/{$this->resource_type}/{$this->resource_id}");
    }
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

class_alias(__NAMESPACE__ . '\\AdminAttachmentsController', 'AdminAttachmentsController');
