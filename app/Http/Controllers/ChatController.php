<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AssistantAgent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function send(Request $request)
    {
        $message = $request->input('message');
        
        // Sử dụng Agent của Laravel AI SDK mới
        $agent = new AssistantAgent();
        
        // Chúng ta có thể tùy chọn sử dụng bộ nhớ hội thoại
        // Để đơn giản trong bản demo này, chúng ta sẽ gửi prompt trực tiếp.
        // Nếu muốn dùng lịch sử: $agent->continue($sessionId)->prompt($message)
        
        try {
            $response = $agent->prompt($message);
            
            return response()->json([
                'message' => $response->text,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
