<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use OpenAI;

class ChatBotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {

            $client = OpenAI::client(env('OPENAI_API_KEY'));

            $response = $client->chat()->create([
                'model' => 'gpt-4.1-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are Vapour Garage AI assistant.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $request->message
                    ]
                ],
                'max_tokens' => 150,
            ]);

            return response()->json([
                'reply' => $response->choices[0]->message->content
            ]);

        } catch (Exception $e) {

            \Log::error($e);

            return response()->json([
                'reply' => 'OpenAI Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}