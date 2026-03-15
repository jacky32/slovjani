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
    $requireV2 = (($request['recaptcha'] ?? '') === 'v2');
    $canBootstrapDefaultAdmin = DefaultAdminBootstrapper::canBootstrap();

    if ($this->auth->isLoggedIn()) {
      $this->addFlash('info', t("sessions.already_logged_in"));
      header("Location: /");
    } else {
      $this->render("sessions/new", [
        "users" => \User::all(),
        "recaptchaEnabled" => $this->recaptchaService?->isEnabled() ?? false,
        "recaptchaV3SiteKey" => $this->recaptchaService?->getV3SiteKey() ?? '',
        "recaptchaV2SiteKey" => $this->recaptchaService?->getV2SiteKey() ?? '',
        "requireRecaptchaV2" => $requireV2,
        "canBootstrapDefaultAdmin" => $canBootstrapDefaultAdmin,
      ]);
    }
  }

  /**
   * One-time action that creates the default admin user on an empty database.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function bootstrap_default_admin($request)
  {
    try {
      $this->verifyCSRF('/login/bootstrap_default_admin');

      if (!DefaultAdminBootstrapper::canBootstrap()) {
        $this->addFlash('error', t('sessions.bootstrap_default_admin.not_available'));
        header('Location: /login');
        return;
      }

      DefaultAdminBootstrapper::ensureExists($this->auth);
      $this->addFlash('success', t('sessions.bootstrap_default_admin.success'));
      header('Location: /login');
    } catch (Exception $exception) {
      $this->addFlash('error', t('sessions.bootstrap_default_admin.failed'));
      Logger::error('Default admin bootstrap action failed: ' . $exception->getMessage());
      header('Location: /login');
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
    $recaptchaResult = $this->recaptchaService?->verifyLogin($request, $_SERVER['REMOTE_ADDR'] ?? null) ?? [
      'success' => true,
      'requires_v2' => false,
      'reason_key' => '',
    ];

    if (!$recaptchaResult['success']) {
      $this->addFlash('error', t($recaptchaResult['reason_key']));
      $location = '/login';
      if ($recaptchaResult['requires_v2']) {
        $location .= '?recaptcha=v2';
      }
      header('Location: ' . $location);
      die();
    }

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
