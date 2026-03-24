<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;
use App\Models\Post;
use App\Models\Voting;
use App\Services\AttachmentMarkupMediaSourceResolver;
use App\Services\EditorMarkupParser;

/**
 * Centralized admin preview controller for live parser previews in forms.
 *
 * @package Controllers
 */
class AdminPreviewsController extends AdminController
{
  /**
   * Parses incoming text and returns JSON HTML preview.
   *
   * Expected request params:
   * - parser: currently supports "editor_markup"
   * - input: raw source text to parse
   */
  public function preview_markup($request)
  {
    $parser = isset($request['parser']) && is_string($request['parser']) ? $request['parser'] : 'editor_markup';
    $input = isset($request['input']) && is_string($request['input']) ? $request['input'] : '';

    if ($parser !== 'editor_markup') {
      $this->viewManager->renderJson([
        'html' => '',
        'error' => t('previews.unsupported_parser'),
      ], 422);
    }

    $resolver = null;
    $resourceType = isset($request['resource_type']) && is_string($request['resource_type']) ? trim($request['resource_type']) : '';
    $resourceId = isset($request['resource_id']) ? (int) $request['resource_id'] : 0;
    $adminContext = isset($request['admin_context']) ? (string) $request['admin_context'] === '1' : true;
    $publicOnly = !isset($request['public_only']) || (string) $request['public_only'] === '1';

    if ($resourceType !== '' && $resourceId > 0) {
      $resourceClass = match ($resourceType) {
        'posts' => Post::class,
        'events' => Event::class,
        'votings' => Voting::class,
        default => null,
      };

      if ($resourceClass !== null) {
        $resolver = new AttachmentMarkupMediaSourceResolver($resourceClass, $resourceId, $resourceType, $adminContext, $publicOnly);
      }
    }

    $html = (new EditorMarkupParser($resolver))->parse($input);
    $this->viewManager->renderJson(['html' => $html]);
  }
}

