<?php

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

    $html = (new EditorMarkupParser())->parse($input);
    $this->viewManager->renderJson(['html' => $html]);
  }
}
