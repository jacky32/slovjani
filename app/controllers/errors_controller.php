<?php

/**
 * Controller for fallback error pages such as 404 responses.
 *
 * @package Controllers
 */
class ErrorsController extends ApplicationController
{


  /**
   * Initialises the errors controller.
   */
  public function __construct()
  {
    parent::__construct();
  }


  /**
   * Renders the 404 Not Found page with a 404 HTTP response code.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function notFound($request)
  {
    http_response_code(404);
    $this->render("layouts/errors/404", []);
  }
}
