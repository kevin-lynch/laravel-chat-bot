<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatGPTController extends Controller
{
    public function sendMessage(Request $request)
    {
        $data = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
        ])
        ->post("https://api.openai.com/v1/chat/completions", [
            "model" => "gpt-3.5-turbo",
            'messages' => [
                [
                    "role" => "user",
                    "content" => $request->input('message')
                ]
            ],
            'temperature' => 0.5,
            "max_tokens" => 150,
            "top_p" => 1.0,
            "frequency_penalty" => 0.52,
            "presence_penalty" => 0.5,
            "stop" => ["11."],
        ])
        ->json();

        \Log::info(['$data[choices]' => $data['choices']]);


        return response()->json($data['choices'][0]['message'], 200, array(), JSON_PRETTY_PRINT);
    }

    public function sendMessageOLD(Request $request)
    {
        \Log::info(['message' => $request->input('message')]);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->post('https://api.openai.com/v1/chat/completions', [
            "model" => "gpt-3.5-turbo",
            'prompt' => $request->input('message'),
            'max_tokens' => 150,
        ]);

        \Log::info(['$response' => $response]);




        return response()->json($response->json());
    }
}
