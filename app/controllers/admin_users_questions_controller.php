<?php

/**
 * Admin controller handling user responses for voting questions.
 *
 * @package Controllers
 */

class AdminUsersQuestionsController extends AdminController
{
  private $voting_id;
  private $users_question_id;

  /**
   * Parses the voting ID and optional users-question ID from the request URI.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/votings\/(\d+)\/users_questions(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->voting_id = $matches[1];
    $this->users_question_id = $matches[2] ?? null;
  }

  /**
   * Renders the ballot submission form for the current voting.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function new($request)
  {
    $voting = Voting::find($this->voting_id);
    $questions = $voting->questions->get();
    $parser = new EditorMarkupParser();
    $parsedQuestionDescriptions = [];
    foreach ($questions as $question) {
      $parsedQuestionDescriptions[$question->id] = $parser->parse($question->description ?? '');
    }
    $pagination = Voting::paginate($request['page'], $this->voting_id);
    $this->render("admin/users_questions/new", [
      "voting" => $voting,
      "votings" => $pagination->resources,
      "pagination" => $pagination,
      "questions" => $questions,
      "parsed_question_descriptions" => $parsedQuestionDescriptions,
    ]);
  }

  /**
   * Persists the user's answers for all questions in the voting.
   *
   * @param array $request Parsed request data including array of question answers.
   * @return void
   */
  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/users_questions');
      $voting = Voting::find($this->voting_id);
      // TODO: Move validation to model?
      Logger::debug("1");
      if ($voting->status != "IN_PROGRESS") {
        throw new Exception(t("users_questions.create.voting_not_active"));
      }
      $valid_question_ids = $voting->questions->pluck('id');
      Logger::debug("2");
      // Create new voting
      foreach ($request['users_question'] as $questionData) {
        if (!in_array($questionData['question_id'], $valid_question_ids)) {
          throw new Exception(t("users_questions.create.invalid_question"));
        }
        Logger::debug("Question ID: " . $questionData['question_id'] . ", Chosen Option: " . $questionData['chosen_option'] . " user id " . $this->auth->getUserId());
        $users_question = new UsersQuestion([
          'chosen_option' => $questionData['chosen_option'],
          'question_id' => $questionData['question_id'],
          'user_id' => $this->auth->getUserId(),
        ]);
        $users_question->save();
      }
      Logger::debug("3");
      $this->addFlash('success', t("users_questions.create.success"));
      header("Location: /admin/votings/" . $this->voting_id);
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
      // if ($e instanceof \ActiveModel\ValidationException) {
      $this->addFlash('error', $e->getMessage());
      $voting = Voting::find($this->voting_id);
      $questions = $voting->questions->get();
      $parser = new EditorMarkupParser();
      $parsedQuestionDescriptions = [];
      foreach ($questions as $question) {
        $parsedQuestionDescriptions[$question->id] = $parser->parse($question->description ?? '');
      }
      $pagination = Voting::paginate($request['page'], $this->voting_id);
      $this->render("admin/users_questions/new", [
        "voting" => $voting,
        "votings" => $pagination->resources,
        "pagination" => $pagination,
        "questions" => $questions,
        "parsed_question_descriptions" => $parsedQuestionDescriptions,
        "errors" => $errors,
      ]);
    }
  }

  /**
   * Retracts the user's vote for a specific answer in the voting.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/users_questions/' . $this->users_question_id . '/destroy');

      // Find voting and check ownership
      $voting = Voting::find($this->voting_id);
      if ($voting->status != "IN_PROGRESS") {
        throw new Exception(t("users_questions.destroy.voting_not_active"));
      }
      $user = User::find($this->auth->getUserId());
      $users_question = $user->users_questions->find($this->users_question_id);
      $allowed_voting_question_ids = $voting->questions->pluck('id');
      if ($voting && $users_question && in_array($users_question->question_id, $allowed_voting_question_ids) && $users_question->user_id == $this->auth->getUserId()) {
        $users_question->destroy();
        $this->addFlash('success', t("users_questions.destroy.success"));
      } else {
        if (!$voting) {
          $this->addFlash('error', t("users_questions.destroy.not_found"));
        } else if ($voting->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', t("users_questions.destroy.unauthorized"));
        }
        $this->addFlash('error', t("error"));
      }
      header("Location: /admin/votings/" . $this->voting_id);
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/votings");
    }
  }
}
