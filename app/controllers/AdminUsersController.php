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
        "title" => (string) ($user->username ?? ''),
        "meta_description_source" => (string) ($user->email ?? ''),
      ]);
    } else {
      $this->addFlash('error', t("users.show.user_not_found"));
      header("Location: /admin/users");
    }
  }

  /**
   * Renders the form for creating a new user in admin.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function new($request)
  {
    if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
      $this->addFlash('error', t('errors.unauthorized'));
      header('Location: /admin/users');
      return;
    }

    $pagination = User::paginate($request['page']);
    $this->render("admin/users/new", [
      "user" => new User(),
      "users" => $pagination->resources,
      "pagination" => $pagination,
    ]);
  }

  /**
   * Creates a new user account from admin and assigns the selected role.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function create($request)
  {
    $userAttributes = $request['user'] ?? [];

    try {
      $this->verifyCSRF('/admin/users');

      if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
        throw new RuntimeException(t('errors.unauthorized'));
      }

      $email = trim((string) ($userAttributes['email'] ?? ''));
      $username = trim((string) ($userAttributes['username'] ?? ''));
      $password = (string) ($userAttributes['password'] ?? '');
      $selectedRole = (string) ($userAttributes['role'] ?? 'none');

      if (!array_key_exists($selectedRole, User::AVAILABLE_ROLES)) {
        throw new RuntimeException(t('users.create.invalid_role'));
      }

      $newUserId = $this->auth->admin()->createUser($email, $password, $username);
      $roleMask = (int) User::AVAILABLE_ROLES[$selectedRole];
      if ($roleMask !== 0) {
        $this->auth->admin()->addRoleForUserById($newUserId, $roleMask);
      }

      $this->addFlash('success', t('users.create.success'));
      header('Location: /admin/users/' . $newUserId);
    } catch (\Delight\Auth\InvalidEmailException) {
      $this->addFlash('error', t('users.create.invalid_email'));
      $this->renderNewWithErrors($request, $userAttributes);
    } catch (\Delight\Auth\InvalidPasswordException) {
      $this->addFlash('error', t('users.create.invalid_password'));
      $this->renderNewWithErrors($request, $userAttributes);
    } catch (\Delight\Auth\UserAlreadyExistsException) {
      $this->addFlash('error', t('users.create.user_already_exists'));
      $this->renderNewWithErrors($request, $userAttributes);
    } catch (\Delight\Auth\DuplicateUsernameException) {
      $this->addFlash('error', t('users.create.username_already_exists'));
      $this->renderNewWithErrors($request, $userAttributes);
    } catch (\Delight\Auth\TooManyRequestsException) {
      $this->addFlash('error', t('users.create.too_many_requests'));
      $this->renderNewWithErrors($request, $userAttributes);
    } catch (\Throwable $exception) {
      $this->addFlash('error', $exception->getMessage());
      $this->renderNewWithErrors($request, $userAttributes);
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

  /**
   * @param array<string, mixed> $request
   * @param array<string, mixed> $userAttributes
   */
  private function renderNewWithErrors(array $request, array $userAttributes): void
  {
    $pagination = User::paginate($request['page'] ?? null);
    $user = new User([
      'email' => $userAttributes['email'] ?? '',
      'username' => $userAttributes['username'] ?? '',
      'roles_mask' => User::AVAILABLE_ROLES[$userAttributes['role'] ?? 'none'] ?? 0,
    ]);

    $this->render('admin/users/new', [
      'user' => $user,
      'users' => $pagination->resources,
      'pagination' => $pagination,
      'errors' => [],
    ]);
  }
}
