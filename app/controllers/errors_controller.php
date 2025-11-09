<?php
class ErrorsController extends ApplicationController
{


  public function __construct()
  {
    parent::__construct();
  }


  public function notFound($request)
  {
    $this->render("layouts/errors/404", []);
  }
}
