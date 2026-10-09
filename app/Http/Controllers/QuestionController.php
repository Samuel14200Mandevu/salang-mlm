<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerQuestionRequest;
use App\Http\Requests\StoreQuestionRequest;
use App\Models\QuestionConversation;
use App\Models\QuestionMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', QuestionConversation::class);

        $user = Auth::user();
        $query = QuestionConversation::query()
            ->with(['member', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at');

        if ($user->hasSupervisionAccess()) {
            if ($request->filled('status') && in_array($request->status, [QuestionConversation::STATUS_OPEN, QuestionConversation::STATUS_RESOLVED], true)) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if (! $user->hasSupervisionAccess()) {
            $conversation = QuestionConversation::query()
                ->where('user_id', $user->id)
                ->first();

            if ($conversation) {
                return redirect()->route('questions.show', $conversation);
            }

            return redirect()->route('questions.create');
        }

        $conversations = $query->paginate(15)->withQueryString();

        return view('questions.index', [
            'conversations' => $conversations,
            'questions' => $conversations,
            'isSupervision' => $user->hasSupervisionAccess(),
            'statusFilter' => $request->get('status'),
        ]);
    }

    public function create()
    {
        $this->authorize('create', QuestionConversation::class);

        $user = Auth::user();
        if (! $user->hasSupervisionAccess()) {
            $existing = QuestionConversation::query()
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                return redirect()->route('questions.show', $existing);
            }
        }

        return view('questions.create');
    }

    public function store(StoreQuestionRequest $request)
    {
        $user = Auth::user();

        $conversation = DB::transaction(function () use ($request, $user) {
            $conversation = QuestionConversation::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['status' => QuestionConversation::STATUS_OPEN]
            );

            if ($conversation->status === QuestionConversation::STATUS_RESOLVED) {
                $conversation->update(['status' => QuestionConversation::STATUS_OPEN]);
            }

            QuestionMessage::query()->create([
                'question_conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'subject' => $request->validated('subject'),
                'body' => $request->validated('body'),
                'created_at' => now(),
            ]);

            $conversation->touchLastMessage();

            return $conversation;
        });

        return redirect()
            ->route('questions.show', $conversation)
            ->with('success', 'Votre message a été envoyé.');
    }

    public function show(QuestionConversation $question)
    {
        $this->authorize('view', $question);

        $question->load(['member', 'messages.sender']);

        return view('questions.show', [
            'conversation' => $question,
            'question' => $question,
            'canAnswer' => Auth::user()->can('answer', $question),
            'canMessage' => Auth::user()->can('message', $question),
        ]);
    }

    public function destroy(QuestionConversation $question)
    {
        $this->authorize('delete', $question);

        $question->delete();

        return redirect()
            ->route('questions.index')
            ->with('success', 'Conversation supprimée.');
    }

    public function answer(AnswerQuestionRequest $request, QuestionConversation $question)
    {
        DB::transaction(function () use ($request, $question) {
            QuestionMessage::query()->create([
                'question_conversation_id' => $question->id,
                'user_id' => Auth::id(),
                'body' => $request->validated('answer'),
                'created_at' => now(),
            ]);

            $question->touchLastMessage();
        });

        return redirect()
            ->route('questions.show', $question)
            ->with('success', 'Message envoyé.');
    }

    public function storeMessage(Request $request, QuestionConversation $question)
    {
        $this->authorize('message', $question);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:5'],
        ]);

        DB::transaction(function () use ($validated, $question) {
            if ($question->status === QuestionConversation::STATUS_RESOLVED) {
                $question->update(['status' => QuestionConversation::STATUS_OPEN]);
            }

            QuestionMessage::query()->create([
                'question_conversation_id' => $question->id,
                'user_id' => Auth::id(),
                'body' => $validated['body'],
                'created_at' => now(),
            ]);

            $question->touchLastMessage();
        });

        return redirect()
            ->route('questions.show', $question)
            ->with('success', 'Message envoyé.');
    }
}
