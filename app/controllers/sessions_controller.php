<?php
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
    // try {
    // $user = new \User();
    // if (isset($request['name'])) {
    //   $user->set_name($request['name']);
    // }
    // if (isset($request['email'])) {
    //   $user->set_email($request['email']);
    // }
    // $user->save();
    // header("Location: /login");
    // } catch (\Exception $e) {
    //   $errors[] = $e->getMessage();
    //   $this->render("sessions/index", [
    //     "users" => \User::all(),
    //     "errors" => $errors,
    //   ]);
    // }
  }

  public function destroy($request)
  {
    $this->auth->logout();
    $this->addFlash('success', "Úspěšně odhlášen");
    header("Location: /");
  }
}
