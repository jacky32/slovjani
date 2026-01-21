<?php
class VotingsController extends ApplicationController
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
    $this->render("votings/index", [
      "votings" => Voting::all() // TODO: Pagination
    ]);
  }

  public function show($request)
  {
    $voting = $this->findVotingById();
    if ($voting) {
      $this->render("votings/show", [
        "voting" => $voting,
        "votings" => Voting::all()
      ]);
    } else {
      $this->addFlash('error', t("posts.show.post_not_found"));
      header("Location: /posts");
    }
  }

  public function new($request)
  {
    $this->render("posts/new", [
      "posts" => Post::all()
    ]);
  }

  public function edit($request)
  {
    $voting = $this->findVotingById();
    if ($voting) {
      $this->render("votings/edit", [
        "voting" => $voting,
        "votings" => Voting::all()
      ]);
    } else {
      $this->addFlash('error', t("votings.show.voting_not_found"));
      header("Location: /votings");
    }
  }

  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/votings/' . $this->parseIdFromUri());

      // Find post and check ownership
      $voting = $this->findVotingById();
      if ($voting && $voting->author_id == $this->auth->getUserId()) {
        $voting->datetime_start = $request['datetime_start'];
        $voting->datetime_end = $request['datetime_end'];
        $voting->save();
        $this->addFlash('success', t("votings.update.success"));
        header("Location: /votings/" . $voting->id);
      } else {
        if (!$voting) {
          $this->addFlash('error', t("votings.show.voting_not_found"));
        } else if ($voting->author_id != $this->auth->getUserId()) { // TODO: Authorization check - move to users role
          $this->addFlash('error', t("votings.update.unauthorized"));
        }
        header("Location: /votings");
      }
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
      $this->render("votings/edit", [
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
      $this->verifyCSRF('/votings');
      // Create new voting
      $voting = new Voting([
        'datetime_start' => $request['datetime_start'],
        'datetime_end' => $request['datetime_end'],
        'author_id' => $this->auth->getUserId()
      ]);
      $voting->save();
      $this->addFlash('success', "Hlasování bylo úspěšně vytvořeno.");
      header("Location: /votings");
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
      $this->render("votings/index", [
        "votings" => Voting::all(),
        "errors" => $errors,
      ]);
    }
  }

  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/votings/destroy');

      // Find voting and check ownership
      $voting = $this->findVotingById();
      if ($voting && $voting->author_id == $this->auth->getUserId()) {
        $voting->destroy();
        $this->addFlash('success', "Hlasování bylo úspěšně smazáno.");
      } else {
        if (!$voting) {
          $this->addFlash('error', "Hlasování neexistuje.");
        } else if ($voting->author_id != $this->auth->getUserId()) {
          $this->addFlash('error', "Nemáte oprávnění smazat toto hlasování.");
        }
        $this->addFlash('error', "Nastala chyba");
      }
      header("Location: /votings");
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /votings");
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
