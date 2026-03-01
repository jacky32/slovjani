<?php
/**
 * @package Controllers
 */
class UsersController extends ApplicationController
{
  private $user;

  public function __construct($userModel)
  {
    parent::__construct();
    $this->user = $userModel;
  }

  public function new($request)
  {
    $this->render("registrations/new", [
      "users" => \User::all()
    ]);
  }

  public function create($request)
  {
    try {
      $this->auth->register($_POST['email'], $_POST['password'], $_POST['username']);
      // , function ($selector, $token) {
      //   echo 'Send ' . $selector . ' and ' . $token . ' to the user (e.g. via email)';
      //   echo '  For emails, consider using the mail(...) function, Symfony Mailer, Swiftmailer, PHPMailer, etc.';
      //   echo '  For SMS, consider using a third-party service and a compatible SDK';
      // });
    } catch (\Delight\Auth\InvalidEmailException $e) {
      $this->addFlash('error', t("registrations.create.invalid_email"));
      header("Location: /registration");
      die();
    } catch (\Delight\Auth\InvalidPasswordException $e) {
      $this->addFlash('error', t("registrations.create.invalid_password"));
      header("Location: /registration");
      die();
    } catch (\Delight\Auth\UserAlreadyExistsException $e) {
      $this->addFlash('error', t("registrations.create.user_already_exists"));
      header("Location: /registration");
      die();
    } catch (\Delight\Auth\TooManyRequestsException $e) {
      $this->addFlash('error', t("registrations.create.too_many_requests"));
      header("Location: /registration");
      die();
    }
    $this->addFlash('success', t("registrations.create.success"));
    header("Location: /login");
  }
}
