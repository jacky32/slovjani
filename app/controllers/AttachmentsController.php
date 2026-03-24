<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;
use App\Models\Post;

/**
 * Public controller for serving attachments attached to visible resources.
 *
 * @package Controllers
 */
class AttachmentsController extends ApplicationController
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

    preg_match('/\/(posts|events)\/(\d+)\/attachments(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->resource_type = $matches[1] ?? null;
    $this->resource_id = $matches[2] ?? null;
    $this->attachment_id = $matches[3] ?? null;
    $this->resource = $this->findResource();
  }

  /**
   * Streams a publicly visible attachment file to the browser.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function show($request)
  {
    $attachment = $this->resource->attachments->where(['is_publicly_visible' => true])->find($this->attachment_id);
    if ($attachment) {
      header("Content-Type: " . $attachment->file_type);
      readfile(getcwd() . '/uploads/' . $attachment->token . '/' . $attachment->file_name);
    } else {
      $this->addFlash('error', t("attachments.show.attachment_not_found"));
      header("Location: /{$this->resource_type}/{$this->resource_id}");
    }
  }

  /**
   * Resolves the parent public resource (Post or Event) from the parsed type and ID.
   *
   * @return Post|Event|null The found resource, or null for unknown types.
   */
  private function findResource(): Post|Event|null
  {
    switch ($this->resource_type) {
      case 'posts':
        return Post::find($this->resource_id);
      case 'events':
        return Event::find($this->resource_id);
      default:
        return null;
    }
  }
}

class_alias(__NAMESPACE__ . '\\AttachmentsController', 'AttachmentsController');
