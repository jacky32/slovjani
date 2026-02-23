<?php
/**
 * @package Controllers
 */
class AdminController extends ApplicationController
{
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
