<?php

namespace App\Http\Controllers;

use App\Services\MemberAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberAssistantController extends Controller
{
    public function __construct(
        protected MemberAssistantService $assistant
    ) {}

    public function index()
    {
        return view('profile.assistant');
    }

    public function search(Request $request)
    {
        $max = (int) config('member-assistant.max_query_length', 200);

        $validated = $request->validate([
            'q' => 'nullable|string|max:' . $max,
            'message' => 'nullable|string|max:' . $max,
        ]);

        $user = Auth::user();
        $query = $validated['message'] ?? $validated['q'] ?? '';
        $reply = $this->assistant->chatReply($user, $query);

        return response()->json([
            'reply' => $reply,
            'context' => $this->assistant->memberContext($user),
        ]);
    }
}
