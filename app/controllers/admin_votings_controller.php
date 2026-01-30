<?php

class AdminVotingsController extends AdminController
{
  private $voting;
  private $id;

  public function __construct($votingModel)
  {
    parent::__construct();
    $this->voting = $votingModel;
  }


  public function index($request)
  {
    $this->render("admin/votings/index", [
      "votings" => Voting::all() // TODO: Pagination
    ]);
  }

  public function show($request)
  {
    $voting = $this->findVotingById();
    if ($voting) {
      $this->render("admin/votings/show", [
        "voting" => $voting,
        "votings" => Voting::all()
      ]);
    } else {
      $this->addFlash('error', t("votings.show.voting_not_found"));
      header("Location: /admin/votings");
    }
  }

  public function new($request)
  {
    $this->render("admin/votings/new", [
      "votings" => Voting::all()
    ]);
  }

  public function edit($request)
  {
    $voting = $this->findVotingById();
    if ($voting) {
      $this->render("admin/votings/edit", [
        "voting" => $voting,
        "votings" => Voting::all()
      ]);
    } else {
      $this->addFlash('error', t("votings.show.voting_not_found"));
      header("Location: /admin/votings");
    }
  }

  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->parseIdFromUri());

      // Find voting and check ownership
      $voting = $this->findVotingById();
      if ($voting && $voting->creator_id == $this->auth->getUserId()) {
        $voting->name = $request['voting']['name'];
        $voting->description = $request['voting']['description'];
        $voting->datetime_start = $request['voting']['datetime_start'];
        $voting->datetime_end = $request['voting']['datetime_end'];
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
      $errors[] = $e->getMessage();
      if ($e instanceof \ActiveModel\ValidationException) {
        $this->addFlash('error', $e->getMessage());
      }
      $this->render("admin/votings/edit", [
        "voting" => $voting,
        "votings" => Voting::all(),
        "errors" => $errors,
      ]);
    }
  }

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
        'creator_id' => $this->auth->getUserId()
      ]);
      $voting->save();
      $this->addFlash('success', "Hlasování bylo úspěšně vytvořeno.");
      header("Location: /admin/votings");
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
      if ($e instanceof \ActiveModel\ValidationException) {
        $this->addFlash('error', $e->getMessage());
      }
      $this->render("admin/votings/index", [
        "votings" => Voting::all(),
        "errors" => $errors,
      ]);
    }
  }

  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/destroy');

      // Find voting and check ownership
      $voting = $this->findVotingById();
      if ($voting && $voting->creator_id == $this->auth->getUserId()) {
        $voting->destroy();
        $this->addFlash('success', "Hlasování bylo úspěšně smazáno.");
      } else {
        if (!$voting) {
          $this->addFlash('error', "Hlasování neexistuje.");
        } else if ($voting->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', "Nemáte oprávnění smazat toto hlasování.");
        }
        $this->addFlash('error', "Nastala chyba");
      }
      header("Location: /admin/votings");
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/votings");
    }
  }

  private function parseIdFromUri()
  {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    preg_match('/votings\/\d+/', $uri, $matches);
    $id = explode('/', $matches[0])[1];
    return $id;
  }

  private function findVotingById()
  {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    preg_match('/votings\/\d+/', $uri, $matches);
    $id = explode('/', $matches[0])[1];
    return Voting::find($id);
  }
}
