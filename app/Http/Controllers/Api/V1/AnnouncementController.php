<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/v1/announcements
     * Return a list of all announcements.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with("user:id,name,role")
                              ->active();

        if ($request->has("category")) {
            $query->byCategory($request->query("category"));
        }
        if ($request->boolean("pinned")) {
            $query->pinned();
        }

        $announcements = $query
            ->orderByDesc("is_pinned")
            ->orderByDesc("created_at")
            ->get();

        return $this->successResponse($announcements, "Announcements retrieved.");
    }

    /**
     * POST /api/v1/announcements
     * Create a new announcement (requires auth).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "title"      => "required|string|max:255",
            "body"       => "required|string",
            "category"   => "required|string|max:100",
            "priority"   => "nullable|in:low,medium,high",
            "is_pinned"  => "nullable|boolean",
            "expires_at" => "nullable|date|after:now",
        ]);

        $announcement = Announcement::create([
            "user_id" => $request->user()->id,
            ...$validated,
        ]);

        $announcement->load("user:id,name,role");

        return $this->successResponse($announcement, "Announcement created.", 201);
    }

    /**
     * GET /api/v1/announcements/{id}
     * Return a single announcement.
     */
    public function show(string $id): JsonResponse
    {
        $ann = Announcement::with("user:id,name,role")->find($id);
        if (!$ann) return $this->notFoundResponse("Announcement");

        return $this->successResponse($ann, "Announcement retrieved.");
    }

    /**
     * PUT/PATCH /api/v1/announcements/{id}
     * Update an existing announcement (requires auth).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $ann = Announcement::find($id);
        if (!$ann) return $this->notFoundResponse("Announcement");

        $validated = $request->validate([
            "title"      => "sometimes|string|max:255",
            "body"       => "sometimes|string",
            "category"   => "sometimes|string|max:100",
            "priority"   => "sometimes|in:low,medium,high",
            "is_pinned"  => "sometimes|boolean",
            "expires_at" => "sometimes|nullable|date",
        ]);

        $ann->update($validated);
        $ann->load("user:id,name,role");

        return $this->successResponse($ann, "Announcement updated.");
    }

    /**
     * DELETE /api/v1/announcements/{id}
     * Delete an announcement (requires auth).
     */
    public function destroy(string $id): JsonResponse
    {
        $ann = Announcement::find($id);
        if (!$ann) return $this->notFoundResponse("Announcement");

        $title = $ann->title;
        $ann->delete();

        return $this->successResponse(null, "\"{$title}\" deleted.");
    }
}
