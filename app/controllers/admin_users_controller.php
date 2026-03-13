<?php

/**
 * Admin controller for listing, editing, and removing user accounts.
 *
 * @package Controllers
 */
class AdminUsersController extends AdminController
{
  private $id;

  /**
   * Parses the user ID from the request URI.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/admin\/users\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  /**
   * Lists all users with pagination.
   *
   * @param array $request Parsed request data (expects 'page' key).
   * @return void
   */
  public function index($request)
  {
    $pagination = User::paginate($request['page']);
    $this->render("admin/users/index", [
      "users" => $pagination->resources,
      "pagination" => $pagination,
    ]);
  }

  /**
   * Shows details for a single user.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function show($request)
  {
    $user = User::find($this->id);
    $pagination = User::paginate($request['page'], $this->id);
    if ($user) {
      $this->render("admin/users/show", [
        "user" => $user,
        "users" => $pagination->resources,
        "pagination" => $pagination,
      ]);
    } else {
      $this->addFlash('error', t("users.show.user_not_found"));
      header("Location: /admin/users");
    }
  }

  /**
   * Renders the edit form for an existing user.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function edit($request)
  {
    $user = User::find($this->id);
    $pagination = User::paginate($request['page'], $this->id);
    Logger::debug($user->roles_mask);
    if ($user) {
      $this->render("admin/users/edit", [
        "user" => $user,
        "users" => $pagination->resources,
        "pagination" => $pagination,
      ]);
    } else {
      $this->addFlash('error', t("users.show.user_not_found"));
      header("Location: /admin/users");
    }
  }

  /**
   * Updates an existing user's email, username, and role.
   *
   * @param array $request Parsed request data including updated user attributes.
   * @return void
   */
  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/users/' . $this->id);

      // Find user and check ownership
      $user = User::find($this->id);
      // if ($user && $this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
      if ($user) {
        $user->email = $request['user']['email'];
        $user->username = $request['user']['username'];
        Logger::debug("Selected role: " . $request['user']['role']);
        $user->roles_mask = intval(User::AVAILABLE_ROLES[$request['user']['role']]) ?? 0;
        $user->save();
        $this->addFlash('success', t("users.update.success"));
        header("Location: /admin/users/" . $user->id);
      }
      // } else {
      //   if (!$user) {
      //     $this->addFlash('error', t("users.show.user_not_found"));
      //   } else if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
      //     $this->addFlash('error', t("users.update.unauthorized"));
      //   }
      //   header("Location: /admin/users/" . $user->id . "/edit");
      // }
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = User::paginate($request['page'], $this->id);
      $this->render("admin/users/edit", [
        "user" => $user,
        "users" => $pagination->resources,
        "pagination" => $pagination,
        "errors" => $errors,
      ]);
    }
  }

  /**
   * Deletes a user account.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/users/' . $this->id . '/destroy');

      // Find user and check ownership
      $user = User::find($this->id);
      if ($user && $user->creator_id == $this->auth->getUserId()) {
        $user->destroy();
        $this->addFlash('success', t("users.destroy.success"));
      } else {
        if (!$user) {
          $this->addFlash('error', t("users.destroy.not_found"));
        } else if ($user->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', t("users.destroy.unauthorized"));
        }
        $this->addFlash('error', t("error"));
      }
      header("Location: /admin/users");
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/users/" . $this->id);
    }
  }
}
