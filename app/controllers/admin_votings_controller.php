<?php

/**
 * Admin CRUD controller for votings and lifecycle management.
 *
 * @package Controllers
 */

class AdminVotingsController extends AdminController
{
  private $id;

  /**
   * Parses the voting ID from the request URI.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/admin\/votings\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  /**
   * Lists all votings with pagination.
   *
   * @param array $request Parsed request data (expects 'page' key).
   * @return void
   */
  public function index($request)
  {
    $pagination = Voting::paginate($request['page']);
    $this->render("admin/votings/index", [
      "votings" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  /**
   * Shows details for a single voting including whether the current user has voted.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function show($request)
  {
    $voting = Voting::find($this->id);
    if ($voting) {
      $pagination = Voting::paginate($request['page'], $this->id);
      $parsedDescription = (new EditorMarkupParser())->parse($voting->description ?? '');
      $this->render("admin/votings/show", [
        "voting" => $voting,
        "votings" => $pagination->resources,
        "pagination" => $pagination,
        "parsed_description" => $parsedDescription,
        "has_voted" => $voting->hasUserVoted($this->auth->getUserId())
      ]);
    } else {
      $this->addFlash('error', t("votings.show.voting_not_found"));
      header("Location: /admin/votings");
    }
  }

  /**
   * Renders the new-voting creation form.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function new($request)
  {
    $pagination = Voting::paginate($request['page']);
    $this->render("admin/votings/new", [
      "votings" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  /**
   * Persists a new voting.
   *
   * @param array $request Parsed request data including voting attributes.
   * @return void
   */
  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings');
      // Create new voting
      $voting = new Voting([
        'name' => $request['voting']['name'],
        'description' => $request['voting']['description'],
        'datetime_start' => $request['voting']['datetime_start'],
        'datetime_end' => $request['voting']['datetime_end'],
        'creator_id' => $this->auth->getUserId(),
        'status' => "DRAFT"
      ]);
      $voting->save();
      $this->addFlash('success', t("votings.create.success"));
      header("Location: /admin/votings");
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = Voting::paginate($request['page']);
      $this->render("admin/votings/new", [
        "voting" => new Voting([
          'name' => $request['voting']['name'],
          'description' => $request['voting']['description'],
          'datetime_start' => $request['voting']['datetime_start'],
          'datetime_end' => $request['voting']['datetime_end']
        ]),
        "votings" => $pagination->resources,
        "pagination" => $pagination,
        "errors" => $errors,
      ]);
    }
  }

  /**
   * Renders the edit form for an existing voting.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function edit($request)
  {
    $voting = Voting::find($this->id);
    if ($voting) {
      $pagination = Voting::paginate($request['page'], $this->id);
      $this->render("admin/votings/edit", [
        "voting" => $voting,
        "votings" => $pagination->resources,
        "pagination" => $pagination,
      ]);
    } else {
      $this->addFlash('error', t("votings.show.voting_not_found"));
      header("Location: /admin/votings");
    }
  }

  /**
   * Updates an existing voting owned by the current user.
   *
   * @param array $request Parsed request data including updated voting attributes.
   * @return void
   */
  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->id);

      // Find voting and check ownership
      $voting = Voting::find($this->id);
      if ($voting && $voting->creator_id == $this->auth->getUserId()) {
        foreach (Voting::getDbAttributes() as $attribute) {
          if (isset($request['voting'][$attribute])) {
            $voting->{$attribute} = $request['voting'][$attribute];
          }
        }
        $voting->save();
        $this->addFlash('success', t("votings.update.success"));
        header("Location: /admin/votings/" . $voting->id);
      } else {
        if (!$voting) {
          $this->addFlash('error', t("votings.show.voting_not_found"));
        } else if ($voting->creator_id != $this->auth->getUserId()) { // TODO: Authorization check - move to users role
          $this->addFlash('error', t("votings.update.unauthorized"));
        }
        header("Location: /admin/votings");
      }
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = Voting::paginate($request['page'], $this->id);
      $this->render("admin/votings/edit", [
        "voting" => $voting,
        "votings" => $pagination->resources,
        "pagination" => $pagination,
        "errors" => $errors,
      ]);
    }
  }

  /**
   * Deletes a voting owned by the current user.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/destroy');

      // Find voting and check ownership
      $voting = Voting::find($this->id);
      if ($voting && $voting->creator_id == $this->auth->getUserId()) {
        $voting->destroy();
        $this->addFlash('success', t("votings.destroy.success"));
      } else {
        if (!$voting) {
          $this->addFlash('error', t("votings.destroy.not_found"));
        } else if ($voting->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', t("votings.destroy.unauthorized"));
        }
        $this->addFlash('error', t("error"));
      }
      header("Location: /admin/votings");
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/votings");
    }
  }
}
