<?php
/**
 * @package Controllers
 */
class ErrorsController extends ApplicationController
{


  public function __construct()
  {
    parent::__construct();
  }


  public function notFound($request)
  {
    http_response_code(404);
    $this->render("layouts/errors/404", []);
  }
}
