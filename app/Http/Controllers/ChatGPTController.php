<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatGPTController extends Controller
{
    public function sendMessage(Request $request)
    {
        $model = $request->input('model', 'gpt-3.5-turbo-1106');

        return $this->handleChatModel($request, $model);
    }

    protected function handleChatModel(Request $request, $model)
    {
        $conversation = $request->input('conversation', []);
        $conversation[] = [
            "role" => "user",
            "content" => $request->input('message')
        ];

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
            return response()->json($data['choices'][0]['message'], 200, array(), JSON_PRETTY_PRINT);
        } else {
            return response()->json(['error' => $data['error'] ?? 'Unknown error'], 500);
        }
    }


    protected function handleCodeModel(Request $request)
    {
        // @TODO add code specific model and response here
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

}
