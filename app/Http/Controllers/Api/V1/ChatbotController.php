<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\ChatbotMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    use ApiResponseTrait;

    /**
     * POST /api/v1/chatbot
     * Process a user message, persist both message and reply to DB,
     * and return the bot response with suggestions.
     * AI integration replaces getRuleBasedResponse() in Module 16.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message'         => ['required', 'string', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'max:100'],
        ]);

        $msg    = strtolower(trim($validated['message']));
        $reply  = $this->getRuleBasedResponse($msg);
        $convId = $validated['conversation_id'] ?? uniqid('conv_', true);

        $record = ChatbotMessage::create([
            'user_id'         => $request->user()->id,
            'message'         => $validated['message'],
            'response'        => $reply,
            'conversation_id' => $convId,
            'tokens_used'     => null, // populated in Module 16
        ]);

        return $this->successResponse([
            'id'              => $record->id,
            'conversation_id' => $convId,
            'user_message'    => $validated['message'],
            'bot_response'    => $reply,
            'bot_name'        => 'Campus',
            'response_at'     => $record->created_at->toDateTimeString(),
            'powered_by'      => 'Rule-based (AI upgrade in Module 16)',
            'suggestions'     => $this->getSuggestions($msg),
        ], 'Response generated.');
    }

    /**
     * GET /api/v1/chatbot/history
     * Return the last 50 chatbot exchanges for the authenticated user.
     */
    public function history(Request $request): JsonResponse
    {
        $messages = ChatbotMessage::where('user_id', $request->user()->id)
                                   ->orderByDesc('created_at')
                                   ->limit(50)
                                   ->get();

        return $this->successResponse($messages, 'Chat history retrieved.');
    }

    // ── Private Helpers ────────────────────────────────────────────────────

    /**
     * Keyword-based rule engine.
     * Module 16 replaces this with an AI API call — persistence layer unchanged.
     */
    private function getRuleBasedResponse(string $m): string
    {
        $rules = [
            ['k' => ['enroll', 'enrollment'],
             'r' => 'Enrollment for 1st Semester is open. Deadline: August 30. Visit portal.university.edu.'],
            ['k' => ['schedule', 'class', 'room'],
             'r' => 'View your class schedule in the Schedule tab of CampusConnect.'],
            ['k' => ['event', 'activity'],
             'r' => 'Upcoming events are in the Events section. RSVP directly from the app!'],
            ['k' => ['scholarship', 'financial aid'],
             'r' => 'Scholarship applications open until September 15. Submit to OSS Building B.'],
            ['k' => ['library', 'book'],
             'r' => 'Library hours: M-F 7AM-9PM, Sat 8AM-5PM. Borrow up to 3 books for 7 days.'],
            ['k' => ['wifi', 'internet'],
             'r' => 'Campus WiFi: UNIV-Student. IT Help Desk: Building C Room 101.'],
            ['k' => ['grade', 'gpa'],
             'r' => 'Grades are in the Student Portal. Midterm grades are released 2 weeks after exams.'],
            ['k' => ['hello', 'hi', 'hey'],
             'r' => 'Hello! I am Campus. Ask me about enrollment, schedules, events, scholarships, library, WiFi, or grades!'],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['k'] as $keyword) {
                if (str_contains($m, $keyword)) {
                    return $rule['r'];
                }
            }
        }

        return 'I did not understand that. Ask me about enrollment, schedules, events, scholarships, library, WiFi, or grades.';
    }

    /**
     * Return contextual follow-up suggestions based on the user message.
     */
    private function getSuggestions(string $m): array
    {
        if (str_contains($m, 'enroll')) {
            return ['Enrollment requirements?', 'Where is the Registrar\'s Office?'];
        }
        if (str_contains($m, 'event')) {
            return ['How do I RSVP?', 'What events are this month?'];
        }
        return ['What events are happening?', 'When is enrollment?', 'How do I connect to WiFi?'];
    }
}