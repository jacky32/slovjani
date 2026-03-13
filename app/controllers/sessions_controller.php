<?php

/**
 * Authentication controller for login and logout session actions.
 *
 * @package Controllers
 */
class SessionsController extends ApplicationController
{

  /**
   * Initialises the sessions controller.
   */
  public function __construct()
  {
    parent::__construct();
  }


  /**
   * Renders the login form, or redirects already-authenticated users.
   *
   * @param array $request Parsed request data.
   * @return void
   */
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

  /**
   * Attempts to log the user in with the submitted email and password.
   *
   * @param array $request Parsed request data.
   * @return void
   */
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

  /**
   * Logs the current user out and redirects to the home page.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function destroy($request)
  {
    $this->auth->logout();
    $this->addFlash('success', t("sessions.successfully_logged_out"));
    header("Location: /");
  }
}
