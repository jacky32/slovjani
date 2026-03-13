<?php

/**
 * Public read-only controller for listing and viewing visible events.
 *
 * @package Controllers
 */
class EventsController extends ApplicationController
{
  private $id;

  /**
   * Parses the event ID from the request URI.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/events\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  /**
   * Lists publicly visible events with pagination.
   *
   * @param array $request Parsed request data (expects 'page' key).
   * @return void
   */
  public function index($request)
  {
    $pagination = Event::where(["is_publicly_visible" => true])->paginate($request['page']);
    $this->render("events/index", [
      "events" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  /**
   * Shows a single publicly visible event with its public attachments.
   *
   * @param array $request Parsed request data.
   * @return void
   */
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
