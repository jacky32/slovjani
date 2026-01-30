<?php

class AdminQuestionsController extends AdminController
{
  private $voting_id;
  private $question_id;

  public function __construct()
  {
    parent::__construct();

    preg_match('/votings\/(\d+)\/questions(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->voting_id = $matches[1];
    $this->question_id = $matches[2] ?? null;
  }

  public function new($request)
  {
    $this->render("admin/questions/new", [
      "voting" => Voting::find($this->voting_id)
    ]);
  }

  public function edit($request)
  {
    $question = Question::find($this->question_id);
    if ($question) {
      $this->render("admin/questions/edit", [
        "question" => $question,
        "voting" => Voting::find($this->voting_id)
      ]);
    } else {
      $this->addFlash('error', t("questions.edit.question_not_found"));
      if ($this->voting_id) {
        header("Location: /admin/votings/" . $this->voting_id);
      } else {
        header("Location: /admin/votings");
      }
    }
  }

  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/questions/' . $this->question_id);

      // Find voting and check ownership
      $voting = Voting::find($this->voting_id);
      $question = $voting->questions->find($this->question_id);
      if ($voting && $question && $voting->creator_id == $this->auth->getUserId()) {
        $question->name = $request['question']['name'];
        $question->description = $request['question']['description'];
        $question->save();
        $this->addFlash('success', t("questions.update.success"));
        header("Location: /admin/votings/" . $voting->id);
      } else {
        if (!$voting) {
          $this->addFlash('error', t("votings.show.voting_not_found"));
        } else if ($voting->creator_id != $this->auth->getUserId()) { // TODO: Authorization check - move to users role
          $this->addFlash('error', t("questions.update.unauthorized"));
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
