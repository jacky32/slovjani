<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Base controller for all admin controllers that handles basic authentication and authorisation.
 *
 * @package Controllers
 */
class AdminController extends ApplicationController
{
  /**
   * Enforces authentication and admin/collaborator role before any action.
   * Redirects to /login if unauthenticated, or to / if unauthorised.
   */
  public function __construct()
  {
    parent::__construct();

    if (!$this->auth->isLoggedIn()) {
      $this->addFlash('error', t("errors.unauthenticated"));
      header("Location: /login");
      exit();
    }

    if (!$this->auth->hasAnyRole(\Delight\Auth\Role::ADMIN, \Delight\Auth\Role::COLLABORATOR)) {
      $this->addFlash('error', t("errors.unauthorized"));
      header("Location: /");
      exit();
    }
  }
}

