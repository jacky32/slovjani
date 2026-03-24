<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Controller for the application landing page.
 *
 * @package Controllers
 */
class HomeController extends ApplicationController
{
  /**
   * Initialises the home controller.
   */
  public function __construct()
  {
    parent::__construct();
  }


  /**
   * Renders the home/index view.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function index($request)
  {
    $this->render("home/index", []);
  }
}

class_alias(__NAMESPACE__ . '\\HomeController', 'HomeController');
