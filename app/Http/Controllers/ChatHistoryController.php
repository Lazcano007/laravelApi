<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\ChatHistory;

class ChatHistoryController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Hämta autentiserad användare (om de är inloggade)
        $user = $request->user();

        // Kontrollera om session_id skickas, annars skapa en ny session
        $session_id = $request->session_id ?? (string) Str::uuid();
        if(!Str::isUuid($session_id)) $session_id = (string) Str::uuid();

        // Hämta tidigare meddelanden från chat_histories
        $previousMessages = ChatHistory::where('user_id', $user->id ?? null)
            ->where('session_id', $session_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($chat) => [
                ['role' => 'user', 'content' => $chat->user_message],
                ['role' => 'assistant', 'content' => $chat->bot_response],
            ])
            ->flatten(1)
            ->toArray();

        // Lägg till användarens nya meddelande
        $messages = array_merge($previousMessages, [
            ['role' => 'user', 'content' => $request->message]
        ]);
        set_time_limit(0);
        // $ch=curl_init();
        // curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        // curl_exec($ch);
        // curl_close($ch);
        
        // Skicka meddelandet till LLM
        $response = Http::timeout(120)->post('http://localhost:11434/api/chat', [
            'model' => 'mistral',
            'messages' => $messages,
            'stream' => false
        ]);

        $data = $response->json();
         // this is how to access data in a nested array
        $botResponse = $data['message']['content'] ?? 'No response from AI';

        // Spara i databasen
        ChatHistory::create([
            'user_id' => $user->id ?? null,
            'session_id' => $session_id,
            'user_message' => $request->message,
            'bot_response' => $botResponse,
        ]);

        return response()->json([
            'session_id' => $session_id,
            'message' => $botResponse
        ]);
    }
}