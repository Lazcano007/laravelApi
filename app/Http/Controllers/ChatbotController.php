<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function chat(Request $request) 
    {
        $request->validate ([
            'message' => 'required|string'
        ]);

        $response = Http::timeout(120)->post('http://localhost:11434/api/generate', [
            'model' => 'mistral',
            'prompt' => $request->message,
            'stream' => false
        ]);
        
        $data = $response->json();

    return response()->json([
            'message' => $data['response'] ?? 'No response from AI'
        ]);  
    }
}
