<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;

class ChatBotController extends Controller
{
    private const SYSTEM_PROMPT = <<<PROMPT
You are the AI assistant for Vapour Garage Main, a vape shop based in Nueva Ecija, Philippines. Est. 2023.

What we sell:
- Disposable vapes (Dispods) — 3,000–8,000 puff devices from ₱280
- Pod systems & refillable kits from ₱1,200
- Box mods (regulated & unregulated) from ₱2,800
- Salt nicotine e-liquids (30–50mg) from ₱380
- Freebase e-liquids (0–6mg) from ₱500
- Coils, pods, wires, cotton, batteries, and accessories
- We also have a loyalty rewards program (Bronze → Silver → Gold → Platinum)

Branches: We have multiple locations across Nueva Ecija (10AM – 9PM daily).
Online shop: https://vapour-garage-shop.test/shop
Track orders: https://vapour-garage-shop.test/shop/track

Rules:
- Only answer questions about our products, branches, orders, loyalty program, and vaping in general.
- Keep answers short and friendly (2–4 sentences max).
- Do not answer unrelated topics (politics, medical advice, etc.).
- Always mention we are 18+ only and comply with Philippine RA 11467.
- If you don't know something, say to contact us on Facebook or visit a branch.
PROMPT;

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        // Rate limiting is enforced by the 'throttle:20,1' middleware on the
        // /chatbot route — no need to duplicate it here.

        try {
            $client = OpenAI::client(config('services.openai.key', env('OPENAI_API_KEY')));

            $response = $client->chat()->create([
                'model'      => 'gpt-4o-mini',   // correct model name
                'messages'   => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user',   'content' => $request->message],
                ],
                'max_tokens'  => 200,
                'temperature' => 0.7,
            ]);

            return response()->json([
                'reply' => $response->choices[0]->message->content,
            ]);

        } catch (\Exception $e) {
            $msg = $e->getMessage();

            // OpenAI rate limit (429) — friendly message
            if (str_contains($msg, '429') || str_contains($msg, 'Rate limit') || str_contains($msg, 'rate_limit')) {
                return response()->json([
                    'reply' => "I'm getting a lot of questions right now! Please try again in a minute. 🙏",
                ], 429);
            }

            // Model not found or bad request
            if (str_contains($msg, '404') || str_contains($msg, 'model')) {
                return response()->json([
                    'reply' => "I'm having trouble connecting. Please visit a branch or message us on Facebook!",
                ], 503);
            }

            logger()->error('ChatBot error', ['error' => $msg, 'ip' => $request->ip()]);

            return response()->json([
                'reply' => "Sorry, I couldn't process that right now. Try again or contact us directly! 😊",
            ], 500);
        }
    }
}
