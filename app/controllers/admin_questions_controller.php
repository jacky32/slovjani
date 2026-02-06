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
      "voting" => Voting::find($this->voting_id),
      "votings" => Voting::all()
    ]);
  }

  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/questions');
      // Create new voting
      $question = new Question([
        'name' => $request['question']['name'],
        'description' => $request['question']['description'],
        'voting_id' => $this->voting_id,
        'creator_id' => $this->auth->getUserId()
      ]);
      $question->save();
      $this->addFlash('success', "Otázka byla úspěšně vytvořena.");
      header("Location: /admin/votings/" . $this->voting_id);
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $this->render("admin/questions/new", [
        "voting" => Voting::find($this->voting_id),
        "votings" => Voting::all(),
        "question" => new Question([
          'name' => $request['question']['name'],
          'description' => $request['question']['description']
        ]),
        "errors" => $errors,
      ]);
    }
  }

  public function edit($request)
  {
    $voting = Voting::find($this->voting_id);
    $question = $voting->questions->find($this->question_id);
    if ($question) {
      $this->render("admin/questions/edit", [
        "question" => $question,
        "voting" => $voting,
        "votings" => Voting::all()
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
    Logger::debug("Updating question with ID: " . $this->question_id . " for voting ID: " . $this->voting_id);
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
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $voting = Voting::find($this->voting_id);
      $this->render("admin/questions/edit", [
        "voting" => $voting,
        "votings" => Voting::all(),
        "question" => $voting->questions->find($this->question_id),
        "errors" => $errors,
      ]);
    }
  }

  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/questions/' . $this->question_id . '/destroy');

      // Find voting and check ownership
      $voting = Voting::find($this->voting_id);
      $question = $voting->questions->find($this->question_id);
      if ($voting && $question && $question->creator_id == $this->auth->getUserId()) {
        $question->destroy();
        $this->addFlash('success', "Otázka byla úspěšně smazána.");
      } else {
        if (!$voting) {
          $this->addFlash('error', "Hlasování neexistuje.");
        } else if ($voting->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', "Nemáte oprávnění smazat tuto otázku.");
        }
        $this->addFlash('error', "Nastala chyba");
      }
      header("Location: /admin/votings/" . $this->voting_id);
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/votings");
    }
  }
}
