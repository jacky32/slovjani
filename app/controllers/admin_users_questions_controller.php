<?php
/**
 * @package Controllers
 */

class AdminUsersQuestionsController extends AdminController
{
  private $voting_id;
  private $users_question_id;

  public function __construct()
  {
    parent::__construct();

    preg_match('/votings\/(\d+)\/users_questions(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->voting_id = $matches[1];
    $this->users_question_id = $matches[2] ?? null;
  }

  public function new($request)
  {
    $voting = Voting::find($this->voting_id);
    $pagination = Voting::paginate($request['page'], $this->voting_id);
    $this->render("admin/users_questions/new", [
      "voting" => $voting,
      "votings" => $pagination->resources,
      "pagination" => $pagination,
      "questions" => $voting->questions->get()
    ]);
  }

  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/users_questions');
      $voting = Voting::find($this->voting_id);
      // TODO: Move validation to model?
      if ($voting->status != "IN_PROGRESS") {
        throw new Exception("Hlasování není aktivní.");
      }
      $valid_question_ids = $voting->questions->pluck('id');
      // Create new voting
      foreach ($request['users_question'] as $questionData) {
        if (!in_array($questionData['question_id'], $valid_question_ids)) {
          throw new Exception("Neplatná otázka pro toto hlasování.");
        }
        $users_question = new UsersQuestion([
          'chosen_option' => $questionData['chosen_option'],
          'question_id' => $questionData['question_id'],
          'user_id' => $this->auth->getUserId(),
        ]);
        $users_question->save();
      }
      $this->addFlash('success', "Úspěšně odhlasováno.");
      header("Location: /admin/votings/" . $this->voting_id);
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
      // if ($e instanceof \ActiveModel\ValidationException) {
      $this->addFlash('error', $e->getMessage());
      $voting = Voting::find($this->voting_id);
      $pagination = Voting::paginate($request['page'], $this->voting_id);
      $this->render("admin/users_questions/new", [
        "voting" => $voting,
        "votings" => $pagination->resources,
        "pagination" => $pagination,
        "questions" => $voting->questions->get(),
        "errors" => $errors,
      ]);
    }
  }

  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/users_questions/' . $this->users_question_id . '/destroy');

      // Find voting and check ownership
      $voting = Voting::find($this->voting_id);
      if ($voting->status != "IN_PROGRESS") {
        throw new Exception("Hlasování není aktivní.");
      }
      $user = User::find($this->auth->getUserId());
      $users_question = $user->users_questions->find($this->users_question_id);
      $allowed_voting_question_ids = $voting->questions->pluck('id');
      if ($voting && $users_question && in_array($users_question->question_id, $allowed_voting_question_ids) && $users_question->user_id == $this->auth->getUserId()) {
        $users_question->destroy();
        $this->addFlash('success', "Vaše hlasování bylo úspěšně smazáno.");
      } else {
        if (!$voting) {
          $this->addFlash('error', "Hlasování neexistuje.");
        } else if ($voting->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', "Nemáte oprávnění smazat toto hlasování.");
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
