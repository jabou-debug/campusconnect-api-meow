<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/v1/notifications
     * Return notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Notification::where("user_id", $user->id);

        if ($request->boolean("unread")) {
            $query->unread();
        }
        if ($request->has("type")) {
            $query->byType($request->query("type"));
        }

        $notifications = $query
            ->orderBy("is_read")
            ->orderByDesc("created_at")
            ->get();

        $unreadCount = Notification::where("user_id", $user->id)->unread()->count();

        return $this->successResponse([
            "unread_count"  => $unreadCount,
            "notifications" => $notifications,
        ], "Notifications retrieved.");
    }

    /**
     * GET /api/v1/notifications/{id}
     * Return a single notification (must belong to authenticated user).
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $notif = Notification::where("user_id", $request->user()->id)->find($id);
        if (!$notif) return $this->notFoundResponse("Notification");

        return $this->successResponse($notif, "Notification retrieved.");
    }

    /**
     * PATCH /api/v1/notifications/{id}/read
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notif = Notification::where("user_id", $request->user()->id)->find($id);
        if (!$notif) return $this->notFoundResponse("Notification");

        $notif->markAsRead();

        return $this->successResponse($notif->fresh(), "Notification marked as read.");
    }

    /**
     * DELETE /api/v1/notifications/{id}
     * Delete a notification (must belong to authenticated user).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notif = Notification::where("user_id", $request->user()->id)->find($id);
        if (!$notif) return $this->notFoundResponse("Notification");

        $notif->delete();

        return $this->successResponse(null, "Notification deleted.");
    }
}
