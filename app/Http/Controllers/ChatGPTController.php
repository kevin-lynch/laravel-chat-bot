<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatGPTController extends Controller
{
    public function sendMessage(Request $request)
    {
        $conversation = $request->input('conversation', []); // Retrieve conversation history

        $conversation[] = [
            "role" => "user",
            "content" => $request->input('message')
        ];

        $model = $request->input('model', 'gpt-3.5-turbo'); // Default to GPT-3.5-Turbo if not specified

        \Log::info(['$model' => $model]);

        $data = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
        ])
        ->timeout(120)
        ->post("https://api.openai.com/v1/chat/completions", [
            "model" => $model,
            'messages' => $conversation,
            'temperature' => 0.5,
            "max_tokens" => 150,
            "top_p" => 1.0,
            "frequency_penalty" => 0.52,
            "presence_penalty" => 0.5,
            "stop" => ["11."],
        ])
        ->json();

        \Log::info(['$data[choices]' => $data]);


        return response()->json($data['choices'][0]['message'], 200, array(), JSON_PRETTY_PRINT);
    }
}
