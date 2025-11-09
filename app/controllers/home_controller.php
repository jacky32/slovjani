<?php

class HomeController extends ApplicationController
{
  public function __construct()
  {
    parent::__construct();
  }


  public function index($request)
  {
    $this->render("home/index", []);
  }
}
