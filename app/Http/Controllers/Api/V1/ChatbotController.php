<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\ChatbotMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    use ApiResponseTrait;

    /**
     * POST /api/v1/chatbot
     * Store a user message and return a response.
     * AI integration will be added in Module 16.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "message"         => "required|string|max:1000",
            "conversation_id" => "nullable|string|max:255",
        ]);

        // Reuse existing conversation or start a new one
        $conversationId = $validated["conversation_id"] ?? Str::uuid()->toString();

        // Module 16: replace this with a real AI API call (OpenAI / Gemini)
        $response = "Hi! I'm the CampusConnect assistant. AI integration will be enabled in Module 16. " .
                    "Your message has been recorded for context building.";

        $chatMessage = ChatbotMessage::create([
            "user_id"         => $request->user()->id,
            "message"         => $validated["message"],
            "response"        => $response,
            "conversation_id" => $conversationId,
        ]);

        return $this->successResponse([
            "conversation_id" => $conversationId,
            "message"         => $chatMessage->message,
            "response"        => $chatMessage->response,
            "created_at"      => $chatMessage->created_at,
        ], "Message processed.");
    }
}
