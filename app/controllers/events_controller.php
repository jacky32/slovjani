<?php
/**
 * @package Controllers
 */
class EventsController extends ApplicationController
{
  private $id;

  public function __construct()
  {
    parent::__construct();

    preg_match('/events\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  public function index($request)
  {
    $pagination = Event::where(["is_publicly_visible" => true])->paginate($request['page']);
    $this->render("events/index", [
      "events" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  public function show($request)
  {
    $event = Event::where(["is_publicly_visible" => true])->find($this->id);
    if ($event) {
      $pagination = Event::where(["is_publicly_visible" => true])->paginate($request['page'], $this->id);
      $attachments = $event->attachments->where(['is_publicly_visible' => true])->get();
      $this->render("events/show", [
        "event" => $event,
        "events" => $pagination->resources,
        "pagination" => $pagination,
        "attachments" => $attachments
      ]);
    } else {
      $this->addFlash('error', t("events.show.event_not_found"));
      header("Location: /events");
    }
  }
}
