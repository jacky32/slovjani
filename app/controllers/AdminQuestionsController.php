<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Question;
use App\Models\Voting;

/**
 * Admin controller for creating and maintaining voting questions.
 *
 * @package Controllers
 */
class AdminQuestionsController extends AdminController
{
  private $voting_id;
  private $question_id;

  /**
   * Parses the voting ID and optional question ID from the request URI.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/votings\/(\d+)\/questions(?:\/(\d+))?/', $_SERVER['REQUEST_URI'], $matches);
    $this->voting_id = $matches[1];
    $this->question_id = $matches[2] ?? null;
  }

  /**
   * Renders the new-question form under the parent voting.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function new($request)
  {
    $pagination = Voting::paginate($request['page'], $this->voting_id);
    $this->render("admin/questions/new", [
      "question" => new Question(),
      "voting" => Voting::find($this->voting_id),
      "votings" => $pagination->resources,
      "pagination" => $pagination,
    ]);
  }

  /**
   * Persists a new question for the parent voting.
   *
   * @param array $request Parsed request data including question attributes.
   * @return void
   */
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
      $this->addFlash('success', t("questions.create.success"));
      header("Location: /admin/votings/" . $this->voting_id);
    } catch (\Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = Voting::paginate($request['page'], $this->voting_id);
      $this->render("admin/questions/new", [
        "voting" => Voting::find($this->voting_id),
        "votings" => $pagination->resources,
        "pagination" => $pagination,
        "question" => new Question([
          'name' => $request['question']['name'],
          'description' => $request['question']['description']
        ]),
        "errors" => $errors,
      ]);
    }
  }

  /**
   * Renders the edit form for an existing question.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function edit($request)
  {
    $voting = Voting::find($this->voting_id);
    $question = $voting->questions->find($this->question_id);
    if ($question) {
      $pagination = Voting::paginate($request['page'], $this->voting_id);
      $this->render("admin/questions/edit", [
        "question" => $question,
        "voting" => $voting,
        "votings" => $pagination->resources,
        "pagination" => $pagination
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

  /**
   * Updates an existing question belonging to the current user's voting.
   *
   * @param array $request Parsed request data including updated question attributes.
   * @return void
   */
  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/questions/' . $this->question_id);

      // Find voting and question
      $voting = Voting::find($this->voting_id);
      $question = $voting->questions->find($this->question_id);
      if ($voting && $question) {
        $question->name = $request['question']['name'];
        $question->description = $request['question']['description'];
        $question->save();
        $this->addFlash('success', t("questions.update.success"));
        header("Location: /admin/votings/" . $voting->id);
      } else {
        if (!$voting) {
          $this->addFlash('error', t("votings.show.voting_not_found"));
        }
        header("Location: /admin/votings");
      }
    } catch (\Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $voting = Voting::find($this->voting_id);
      $pagination = Voting::paginate($request['page'], $this->voting_id);
      $this->render("admin/questions/edit", [
        "voting" => $voting,
        "votings" => $pagination->resources,
        "pagination" => $pagination,
        "question" => $voting->questions->find($this->question_id),
        "errors" => $errors,
      ]);
    }
  }

  /**
   * Deletes a question from the parent voting.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/votings/' . $this->voting_id . '/questions/' . $this->question_id . '/destroy');

      // Find voting and check ownership
      $voting = Voting::find($this->voting_id);
      $question = $voting->questions->find($this->question_id);
      if ($voting && $question) {
        $question->destroy();
        $this->addFlash('success', t("questions.destroy.success"));
      } else {
        if (!$voting) {
          $this->addFlash('error', t("votings.show.voting_not_found"));
        }
        $this->addFlash('error', t("error"));
      }
      header("Location: /admin/votings/" . $this->voting_id);
    } catch (\Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/votings");
    }
  }
}
