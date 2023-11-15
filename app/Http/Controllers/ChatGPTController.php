<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;

class ChatGPTController extends Controller
{
    public function sendMessage(Request $request)
    {
        $model = $request->input('model', 'gpt-3.5-turbo-1106');

        return $this->handleChatModel($request, $model);
    }

    protected function handleChatModel(Request $request, $model)
    {
        $threadId = $request->input('thread_id', 0);
        $message = $request->input('message');
        $conversation = $request->input('conversation', []);
        $conversation[] = [
            "role" => "user",
            "content" => $message
        ];

        $this->saveMessageToDb('user', $message, $threadId);

        $payload = [
            "model" => $model,
            'messages' => $conversation,
            'temperature' => 0.5,
            "max_tokens" => 500,
            "top_p" => 1.0,
            "frequency_penalty" => 0.52,
            "presence_penalty" => 0.5,
            "stop" => ["11."],
        ];

        $endpoint = "https://api.openai.com/v1/chat/completions";
        $data = $this->sendOpenAIRequest($endpoint, $payload);

        if (isset($data['choices'])) {
            $apiResponse = $data['choices'][0]['message'];

            $this->saveMessageToDb('assistant', $apiResponse['content'], $threadId);

            return response()->json($apiResponse, 200, array(), JSON_PRETTY_PRINT);
        } else {
            return response()->json(['error' => $data['error'] ?? 'Unknown error'], 500);
        }
    }

    protected function saveMessageToDb($role, $message, $threadId)
    {
        return ChatMessage::create([
            'thread_id' => $threadId,
            'role' => $role,
            'message' => $message
        ]);
    }

    protected function sendOpenAIRequest($endpoint, $payload)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
        ])
            ->timeout(120)
            ->post($endpoint, $payload);

        if ($response->failed()) {
            return ['error' => 'Request to OpenAI failed.'];
        }

        return $response->json();
    }

    public function fetchConversation($threadId)
    {
        $conversation = ChatMessage::where(['thread_id' => $threadId])->get();

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

}
