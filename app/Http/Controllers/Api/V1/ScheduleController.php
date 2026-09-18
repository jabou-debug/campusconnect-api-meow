<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/v1/schedules
     * Return the authenticated user's weekly schedule, grouped by day.
     * Days are ordered Monday → Sunday via MySQL FIELD().
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Schedule::where('user_id', $user->id)->currentSemester();

        if ($request->has('day')) {
            $query->forDay(ucfirst(strtolower($request->query('day'))));
        }

        $schedules = $query
            ->orderByRaw("FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->orderBy('time_start')
            ->get();

        $grouped = $schedules->groupBy('day');
        $days    = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $weeklySchedule = collect($days)->map(fn($day) => [
            'day'     => $day,
            'classes' => $grouped->get($day, collect())->values(),
        ]);

        $unique = $schedules->unique('subject_code');

        return $this->successResponse([
            'student' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'student_id' => $user->student_id,
            ],
            'weekly_schedule' => $weeklySchedule,
            'summary' => [
                'total_subjects' => $unique->count(),
                'total_units'    => $unique->sum('units'),
                'school_year'    => '2025-2026',
                'semester'       => '1st',
            ],
        ], 'Schedule retrieved.');
    }
}