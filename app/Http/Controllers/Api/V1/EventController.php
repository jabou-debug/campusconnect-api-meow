<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/v1/events
     * Return all events, optionally filtered by category or status.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::with("organizer:id,name,role");

        if ($request->has("category")) {
            $query->where("category", $request->query("category"));
        }
        if ($request->has("status")) {
            $query->where("status", $request->query("status"));
        }

        $events = $query->orderBy("date")->get();

        return $this->successResponse($events, "Events retrieved.");
    }

    /**
     * POST /api/v1/events
     * Create a new event (requires auth).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "title"                 => "required|string|max:255",
            "description"           => "required|string",
            "location"              => "required|string|max:255",
            "date"                  => "required|date",
            "time_start"            => "required|date_format:H:i:s",
            "time_end"              => "required|date_format:H:i:s|after:time_start",
            "category"              => "required|string|max:100",
            "status"                => "nullable|in:upcoming,ongoing,past,cancelled",
            "capacity"              => "nullable|integer|min:1",
            "is_free"               => "nullable|boolean",
            "contact_email"         => "nullable|email",
            "registration_deadline" => "nullable|date|before:date",
        ]);

        $event = Event::create([
            "user_id" => $request->user()->id,
            ...$validated,
        ]);

        $event->load("organizer:id,name,role");

        return $this->successResponse($event, "Event created.", 201);
    }

    /**
     * GET /api/v1/events/{id}
     * Return a single event by ID.
     */
    public function show(string $id): JsonResponse
    {
        $event = Event::with("organizer:id,name,role")->find($id);
        if (!$event) return $this->notFoundResponse("Event");

        return $this->successResponse($event, "Event retrieved.");
    }

    /**
     * PUT/PATCH /api/v1/events/{id}
     * Update an existing event (requires auth).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $event = Event::find($id);
        if (!$event) return $this->notFoundResponse("Event");

        $validated = $request->validate([
            "title"                 => "sometimes|string|max:255",
            "description"           => "sometimes|string",
            "location"              => "sometimes|string|max:255",
            "date"                  => "sometimes|date",
            "time_start"            => "sometimes|date_format:H:i:s",
            "time_end"              => "sometimes|date_format:H:i:s",
            "category"              => "sometimes|string|max:100",
            "status"                => "sometimes|in:upcoming,ongoing,past,cancelled",
            "capacity"              => "sometimes|integer|min:1",
            "is_free"               => "sometimes|boolean",
            "contact_email"         => "sometimes|nullable|email",
            "registration_deadline" => "sometimes|nullable|date",
        ]);

        $event->update($validated);
        $event->load("organizer:id,name,role");

        return $this->successResponse($event, "Event updated.");
    }

    /**
     * DELETE /api/v1/events/{id}
     * Delete an event (requires auth).
     */
    public function destroy(string $id): JsonResponse
    {
        $event = Event::find($id);
        if (!$event) return $this->notFoundResponse("Event");

        $title = $event->title;
        $event->delete();

        return $this->successResponse(null, "\"{$title}\" deleted.");
    }

    /**
     * POST /api/v1/events/{id}/rsvp
     * RSVP the authenticated user to an event (requires auth).
     */
    public function rsvp(Request $request, string $id): JsonResponse
    {
        $event = Event::find($id);
        if (!$event) return $this->notFoundResponse("Event");

        $user = $request->user();

        // Prevent duplicate RSVPs
        $existing = EventRsvp::where("user_id", $user->id)
                              ->where("event_id", $event->id)
                              ->first();
        if ($existing) {
            return $this->errorResponse("Already RSVPed to this event.", 409);
        }

        // Check capacity
        if ($event->is_full) {
            return $this->errorResponse("Event is fully booked.", 409);
        }

        $rsvp = EventRsvp::create([
            "user_id"  => $user->id,
            "event_id" => $event->id,
            "status"   => "confirmed",
            "rsvp_at"  => now(),
        ]);

        return $this->successResponse([
            "rsvp_id"     => $rsvp->id,
            "event_title" => $event->title,
            "event_date"  => $event->date->toDateString(),
            "user_name"   => $user->name,
            "status"      => "confirmed",
        ], "RSVP confirmed for \"{$event->title}\".", 201);
    }
}
