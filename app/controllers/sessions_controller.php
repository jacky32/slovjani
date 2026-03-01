<?php

/**
 * @package Controllers
 */
class SessionsController extends ApplicationController
{

  public function __construct()
  {
    parent::__construct();
  }


  public function new($request)
  {
    if ($this->auth->isLoggedIn()) {
      $this->addFlash('info', t("sessions.already_logged_in"));
      header("Location: /");
    } else {
      $this->render("sessions/new", [
        "users" => \User::all()
      ]);
    }
  }

  public function create($request)
  {
    try {
      $this->auth->login($_POST['email'], $_POST['password']);
    } catch (\Delight\Auth\InvalidEmailException $e) {
      $this->addFlash('error', t("sessions.invalid_email"));
      header("Location: /login");
      die();
    } catch (\Delight\Auth\InvalidPasswordException $e) {
      $this->addFlash('error', t("sessions.invalid_password"));
      header("Location: /login");
      die();
    } catch (\Delight\Auth\EmailNotVerifiedException $e) {
      $this->addFlash('error', t("sessions.email_not_verified"));
      header("Location: /login");
      die();
    } catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->addFlash('error', t("sessions.too_many_requests"));
      header("Location: /login");
      die();
    }
    $this->addFlash('success', t("sessions.successfully_logged_in"));
    header("Location: /");
  }

  public function destroy($request)
  {
    $this->auth->logout();
    $this->addFlash('success', t("sessions.successfully_logged_out"));
    header("Location: /");
  }
}
