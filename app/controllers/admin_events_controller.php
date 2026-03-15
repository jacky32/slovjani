<?php

/**
 * Admin CRUD controller for events and related publishing actions.
 *
 * @package Controllers
 */

class AdminEventsController extends AdminController
{
  private $id;

  /**
   * Parses the event ID from the request URI.
   */
  public function __construct()
  {
    parent::__construct();

    preg_match('/admin\/events\/(\d+)/', $_SERVER['REQUEST_URI'], $matches);
    $this->id = $matches[1] ?? null;
  }


  /**
   * Lists all events with pagination.
   *
   * @param array $request Parsed request data (expects 'page' key).
   * @return void
   */
  public function index($request)
  {
    $pagination = Event::orderBy('created_at', 'desc')->paginate($request['page']);
    $this->render("admin/events/index", [
      "events" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  /**
   * Shows details for a single event.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function show($request)
  {
    $event = Event::find($this->id);
    if ($event) {
      $pagination = Event::orderBy('created_at', 'desc')->paginate($request['page'], $this->id);
      $parsedDescription = (new EditorMarkupParser(
        new AttachmentMarkupMediaSourceResolver(Event::class, $event->id, 'events', true, true)
      ))->parse($event->description ?? '');
      $this->render("admin/events/show", [
        "event" => $event,
        "events" => $pagination->resources,
        "pagination" => $pagination,
        "parsed_description" => $parsedDescription,
      ]);
    } else {
      $this->addFlash('error', t("events.show.event_not_found"));
      header("Location: /admin/events");
    }
  }

  /**
   * Renders the new-event creation form.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function new($request)
  {
    $pagination = Event::orderBy('created_at', 'desc')->paginate($request['page']);
    $this->render("admin/events/new", [
      "events" => $pagination->resources,
      "pagination" => $pagination
    ]);
  }

  /**
   * Persists a new event and regenerates static pages.
   *
   * @param array $request Parsed request data including event attributes.
   * @return void
   */
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
      try {
        $google_event = (new GoogleCalendarService($event->is_publicly_visible))->insertTimedEvent($event->name, $event->datetime_start, $event->datetime_end);
        $event->google_calendar_event_id = $google_event['id'] ?? null;
      } catch (Exception $e) {
        Logger::error("Failed to create event in Google Calendar: " . $e->getMessage());
        $this->addFlash('error', t("events.create.google_calendar_creation_failed"));
      }
      $event->save();
      (new StaticPageGenerator())->regenerateAll();
      $this->addFlash('success', t("events.create.success"));
      header("Location: /admin/events");
    } catch (Exception $e) {
      $errors = [];
      Logger::error("Failed to create event: " . $e->getMessage());
      $this->addFlash('error', $e->getMessage());
      if ($e instanceof \ActiveModel\ValidationException) {
        $errors = array_merge($errors, $e->getValidationExceptions());
      }
      $pagination = Event::orderBy('created_at', 'desc')->paginate($request['page']);
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

  /**
   * Renders the edit form for an existing event.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function edit($request)
  {
    $event = Event::find($this->id);
    if ($event) {
      $pagination = Event::orderBy('created_at', 'desc')->paginate($request['page'], $this->id);
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

  /**
   * Updates an existing event and regenerates static pages.
   *
   * @param array $request Parsed request data including updated event attributes.
   * @return void
   */
  public function update($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/events/' . $this->id);

      // Find event and check ownership
      $event = Event::find($this->id);
      $previous_publicly_visible = $event ? (bool) $event->is_publicly_visible : false;
      if ($event && $event->creator_id == $this->auth->getUserId()) {
        foreach (Event::getDbAttributes() as $attribute) {
          if ($attribute == "is_publicly_visible") {
            $event->{$attribute} = $request['event'][$attribute] ? true : 0;
          } else if (isset($request['event'][$attribute])) {
            $event->{$attribute} = $request['event'][$attribute];
          }
        }
        if ($event->google_calendar_event_id && ($previous_publicly_visible != $event->is_publicly_visible)) {
          try {
            (new GoogleCalendarService($previous_publicly_visible))->destroyEvent(
              $event->google_calendar_event_id,
              $event->name,
              $event->datetime_start,
              $event->datetime_end
            );
            $google_event = (new GoogleCalendarService($event->is_publicly_visible))->insertTimedEvent(
              $event->name,
              $event->datetime_start,
              $event->datetime_end
            );
            $event->google_calendar_event_id = $google_event['id'] ?? null;
          } catch (Exception $e) {
            Logger::error("Failed to update Google Calendar event with ID " . $event->google_calendar_event_id . ": " . $e->getMessage());
            $this->addFlash('error', t("events.update.google_calendar_update_failed"));
          }
        }
        $event->save();
        (new StaticPageGenerator())->regenerateAll();
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
      $pagination = Event::orderBy('created_at', 'desc')->paginate($request['page'], $this->id);
      $this->render("admin/events/edit", [
        "event" => $event,
        "events" => $pagination->resources,
        "pagination" => $pagination,
        "errors" => $errors,
      ]);
    }
  }

  /**
   * Deletes an event and triggers static page regeneration if the event was public.
   *
   * @param array $request Parsed request data.
   * @return void
   */
  public function destroy($request)
  {
    try {
      // Verify CSRF token
      $this->verifyCSRF('/admin/events/destroy');

      // Find event and check ownership
      $event = Event::find($this->id);
      if ($event && $event->creator_id == $this->auth->getUserId()) {
        $wasPubliclyVisible = (bool) $event->is_publicly_visible;
        if ($event->google_calendar_event_id) {
          try {
            (new GoogleCalendarService($event->is_publicly_visible))->destroyEvent($event->google_calendar_event_id);
          } catch (Exception $e) {
            Logger::error("Failed to delete Google Calendar event with ID " . $event->google_calendar_event_id . ": " . $e->getMessage());
            $this->addFlash('error', t("events.destroy.google_calendar_deletion_failed"));
          }
        }
        $event->destroy();
        if ($wasPubliclyVisible) {
          (new StaticPageGenerator())->regenerateAll();
        }
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
