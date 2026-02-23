<?php
/**
 * @package Controllers
 */

class AdminEventsController extends AdminController
{
  private $id;

  public function __construct()
  {
    parent::__construct();

    preg_match('/admin\/events\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  public function index($request)
  {
    $pagination = Event::paginate($request['page']);
    $this->render("admin/events/index", [
      "events" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  public function show($request)
  {
    $event = Event::find($this->id);
    if ($event) {
      $pagination = Event::paginate($request['page'], $this->id);
      $this->render("admin/events/show", [
        "event" => $event,
        "events" => $pagination->resources,
        "pagination" => $pagination
      ]);
    } else {
      $this->addFlash('error', t("events.show.event_not_found"));
      header("Location: /admin/events");
    }
  }

  public function new($request)
  {
    $pagination = Event::paginate($request['page']);
    $this->render("admin/events/new", [
      "events" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  public function create($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/events');
      // Create new event
      $event = new Event([
        'name' => $request['event']['name'],
        'description' => $request['event']['description'],
        'datetime_start' => $request['event']['datetime_start'],
        'datetime_end' => $request['event']['datetime_end'],
        'is_publicly_visible' => isset($request['event']['is_publicly_visible']) ? true : 0,
        'creator_id' => $this->auth->getUserId()
      ]);
      $event->save();
      $this->addFlash('success', t("events.create.success"));
      header("Location: /admin/events");
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = Event::paginate($request['page']);
      $this->render("admin/events/new", [
        "event" => new Event([
          'name' => $request['event']['name'],
          'description' => $request['event']['description'],
          'datetime_start' => $request['event']['datetime_start'],
          'datetime_end' => $request['event']['datetime_end']
        ]),
        "events" => $pagination->resources,
        "pagination" => $pagination,
        "errors" => $errors,
      ]);
    }
  }

  public function edit($request)
  {
    $event = Event::find($this->id);
    if ($event) {
      $pagination = Event::paginate($request['page'], $this->id);
      $this->render("admin/events/edit", [
        "event" => $event,
        "events" => $pagination->resources,
        "pagination" => $pagination,
      ]);
    } else {
      $this->addFlash('error', t("events.show.event_not_found"));
      header("Location: /admin/events");
    }
  }

  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/events/' . $this->id);

      // Find event and check ownership
      $event = Event::find($this->id);
      if ($event && $event->creator_id == $this->auth->getUserId()) {
        foreach (Event::getDbAttributes() as $attribute) {
          if ($attribute == "is_publicly_visible") {
            $event->{$attribute} = $request['event'][$attribute] ? true : 0;
          } else if (isset($request['event'][$attribute])) {
            $event->{$attribute} = $request['event'][$attribute];
          }
        }
        $event->save();
        $this->addFlash('success', t("events.update.success"));
        header("Location: /admin/events/" . $event->id);
      } else {
        if (!$event) {
          $this->addFlash('error', t("events.show.event_not_found"));
        } else if ($event->creator_id != $this->auth->getUserId()) { // TODO: Authorization check - move to users role
          $this->addFlash('error', t("events.update.unauthorized"));
        }
        header("Location: /admin/events");
      }
    } catch (Exception $e) {
      $errors = [];
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = Event::paginate($request['page'], $this->id);
      $this->render("admin/events/edit", [
        "event" => $event,
        "events" => $pagination->resources,
        "pagination" => $pagination,
        "errors" => $errors,
      ]);
    }
  }

  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/events/destroy');

      // Find event and check ownership
      $event = Event::find($this->id);
      if ($event && $event->creator_id == $this->auth->getUserId()) {
        $event->destroy();
        $this->addFlash('success', t("events.destroy.success"));
      } else {
        if (!$event) {
          $this->addFlash('error', t("events.destroy.event_not_found"));
        } else if ($event->creator_id != $this->auth->getUserId()) {
          $this->addFlash('error', t("events.destroy.unauthorized"));
        }
        $this->addFlash('error', t("error"));
      }
      header("Location: /admin/events");
    } catch (Exception $e) {
      $this->addFlash('error', $e->getMessage());
      header("Location: /admin/events");
    }
  }
}
