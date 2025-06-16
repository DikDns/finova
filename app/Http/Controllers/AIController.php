<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Models\AiMessage;
use App\Models\Budget;
use App\Models\MonthlyBudget;
use App\Models\CategoryGroup;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    public function getChats(Request $request)
    {
        $userId = Auth::id();
        $chats = AiChat::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['chats' => $chats]);
    }

    public function getChatMessages(Request $request, $chatId)
    {
        $userId = Auth::id();
        $chat = AiChat::where('id', $chatId)
            ->where('user_id', $userId)
            ->with('messages')
            ->first();

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        return response()->json([
            'chat' => $chat,
            'messages' => $chat->messages
        ]);
    }

    public function deleteChat(Request $request, $chatId)
    {
        $userId = Auth::id();
        $chat = AiChat::where('id', $chatId)
            ->where('user_id', $userId)
            ->first();

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $chat->delete();
        return response()->json(['success' => true]);
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');
        $chatHistory = $request->input('chat_history', []);
        $budgetId = $request->input('budget_id');
        $chatId = $request->input('chat_id');

        // Prepare budget data if budget_id is provided
        $budgetContext = '';
        if ($budgetId) {
            $budget = Budget::with(['monthlyBudgets' => function ($query) {
                $query->orderBy('month', 'desc')->limit(3);
            }, 'categoryGroups.categories'])->find($budgetId);

            if ($budget) {
                $budgetContext = "Budget Information:\n";
                $budgetContext .= "Name: {$budget->name}\n";
                $budgetContext .= "Currency: {$budget->currency_code}\n\n";

                // Add monthly budget information
                $budgetContext .= "Recent Monthly Budgets:\n";
                foreach ($budget->monthlyBudgets as $monthlyBudget) {
                    $budgetContext .= "- {$monthlyBudget->month}: Total Balance: {$monthlyBudget->total_balance}\n";
                }
                $budgetContext .= "\n";

                // Add category groups and categories
                $budgetContext .= "Category Groups and Categories:\n";
                foreach ($budget->categoryGroups as $group) {
                    $budgetContext .= "- Group: {$group->name}\n";
                    foreach ($group->categories as $category) {
                        $budgetContext .= "  - Category: {$category->name}\n";
                    }
                }
            }
        }

        // Prepare the chat history for Gemini API
        $formattedHistory = [];
        foreach ($chatHistory as $msg) {
            $formattedHistory[] = [
                'role' => $msg['role'],
                'parts' => [['text' => $msg['content']]],
            ];
        }

        try {
            // Prepare user message with budget context if available
            $userMessage = $message;
            if (!empty($budgetContext)) {
                $userMessage = "[CONTEXT]\n{$budgetContext}\n[/CONTEXT]\n\nUser Question: {$message}";
            }

            // Call Gemini API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . env('GEMINI_API_KEY'), [
                'contents' => [
                    ...$formattedHistory,
                    [
                        'role' => 'user',
                        'parts' => [['text' => $userMessage]],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                ],
            ]);

            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak dapat memproses permintaan Anda saat ini.';

            // Get existing chat or create new one
            if ($chatId) {
                $aiChat = AiChat::where('id', $chatId)
                    ->where('user_id', Auth::id())
                    ->first();
                
                if (!$aiChat) {
                    // If chat ID is invalid, create a new chat
                    $aiChat = AiChat::create([
                        'user_id' => Auth::id(),
                        'title' => substr($message, 0, 30) . (strlen($message) > 30 ? '...' : ''),
                        'budget_id' => $budgetId,
                        'category_ids' => [],
                        'transaction_ids' => [],
                        'account_ids' => [],
                    ]);
                }
            } else {
                // Create a new chat if no chat ID provided
                $aiChat = AiChat::create([
                    'user_id' => Auth::id(),
                    'title' => substr($message, 0, 30) . (strlen($message) > 30 ? '...' : ''),
                    'budget_id' => $budgetId,
                    'category_ids' => [],
                    'transaction_ids' => [],
                    'account_ids' => [],
                ]);
            }

            // Save user message
            AiMessage::create([
                'ai_chat_id' => $aiChat->id,
                'content' => $message,
                'role' => 'user',
            ]);

            // Save AI response
            AiMessage::create([
                'ai_chat_id' => $aiChat->id,
                'content' => $reply,
                'role' => 'assistant',
            ]);

            return response()->json([
                'reply' => $reply,
                'chat_id' => $aiChat->id
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get response from AI service: ' . $e->getMessage()], 500);
        }
    }
}
