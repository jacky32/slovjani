<?php
class EventsController extends ApplicationController
{
  private $id;

  public function __construct()
  {
    parent::__construct();

    preg_match('/events\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  public function index()
  {
    $this->render("events/index", [
      "events" => Event::all() // TODO: Pagination
    ]);
  }

  public function show()
  {
    $event = Event::find($this->id);
    if ($event) {
      $this->render("events/show", [
        "event" => $event,
        "events" => Event::all()
      ]);
    } else {
      $this->addFlash('error', t("events.show.event_not_found"));
      header("Location: /events");
    }
  }
}
