<?php
class AttachmentsController extends AdminController
{
  private $resource_type;
  private $resource_id;
  private $attachment_id;
  private $resource;

  public function __construct()
  {
    parent::__construct();

    preg_match('/\/(posts|events)\/(\d+)\/attachments(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->resource_type = $matches[1] ?? null;
    $this->resource_id = $matches[2] ?? null;
    $this->attachment_id = $matches[3] ?? null;
    $this->resource = $this->findResource();
  }

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

  private function findResource()
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
