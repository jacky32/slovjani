<?php

class AdminVotingsController extends AdminController
{
  private $id;

  public function __construct()
  {
    parent::__construct();

    preg_match('/admin\/votings\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  public function index($request)
  {
    $this->render("admin/votings/index", [
      "votings" => Voting::all() // TODO: Pagination
    ]);
  }

  public function show($request)
  {
    $voting = Voting::find($this->id);
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
    $voting = Voting::find($this->id);
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
      $this->verifyCSRF('/admin/votings/' . $this->id);

      // Find voting and check ownership
      $voting = Voting::find($this->id);
      if ($voting && $voting->creator_id == $this->auth->getUserId()) {
        foreach (Voting::getDbAttributes() as $attribute) {
          // Logger::debug("Updating attribute: " . $attribute . " from " . $voting->{$attribute});
          if (isset($request['voting'][$attribute])) {
            // Logger::debug("Setting " . $attribute . " to " . $request['voting'][$attribute]);
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
      $voting = Voting::find($this->id);
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
}
