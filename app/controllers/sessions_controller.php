<?php
/**
 * @package Controllers
 */
class SessionsController extends ApplicationController
{
  private $user;

  public function __construct($userModel)
  {
    parent::__construct();
    $this->user = $userModel;
  }


  public function new($request)
  {
    if ($this->auth->isLoggedIn()) {
      $this->addFlash('info', "Již přihlášen");
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
      $this->addFlash('error', "Špatný email");
      header("Location: /login");
      die();
    } catch (\Delight\Auth\InvalidPasswordException $e) {
      $this->addFlash('error', "Špatné heslo");
      header("Location: /login");
      die();
    } catch (\Delight\Auth\EmailNotVerifiedException $e) {
      $this->addFlash('error', "Email nebyl ověřen");
      header("Location: /login");
      die();
    } catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->addFlash('error', "Příliš mnoho pokusů");
      header("Location: /login");
      die();
    }
    $this->addFlash('success', "Úspěšně přihlášen");
    header("Location: /");
  }

  public function destroy($request)
  {
    $this->auth->logout();
    $this->addFlash('success', "Úspěšně odhlášen");
    header("Location: /");
  }
}
