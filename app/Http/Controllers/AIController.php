<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\AiChat;

class AIController extends Controller
{
    public function chat(Request $request) {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = $request->user();
        $userMessage = $request->input('message');

        $geminiResponse = Http::post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . env('GEMINI_API_KEY'),
            [
                'contents' => [[
                    'parts' => [[
                        'text' => 'Balas menggunakan bahasa INDONESIA yang santai dan tidak kaku, ' . $userMessage
                    ]]
                ]],
            ]
        );

        $reply = $geminiResponse->json('candidates.0.content.parts.0.text') ?? 'Maaf, tidak ada respons.';

        AiChat::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $userMessage,
            'category_ids' => json_encode([]),
            'transaction_ids' => json_encode([]),
            'account_ids' => json_encode([]),
        ]);

        AiChat::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => $reply,
            'category_ids' => json_encode([]),
            'transaction_ids' => json_encode([]),
            'account_ids' => json_encode([]),
        ]);

        return response()->json([
            'raw' => $geminiResponse->json(),
            'reply' => $reply ?? 'Maaf, tidak ada respons.'
        ]);
    }
}
