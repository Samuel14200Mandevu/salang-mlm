<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\ActivityEntry;
use App\Models\ActivitySheet;
use App\Models\ContactMessage;
use App\Models\Publication;
use App\Models\QuestionConversation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function index(): View
    {
        $todaySheet = ActivitySheet::query()
            ->whereDate('activity_date', now()->toDateString())
            ->first();

        $stats = [
            'messages_new' => ContactMessage::query()
                ->where('status', ContactMessage::STATUS_NEW)
                ->count(),
            'messages_total' => ContactMessage::query()->count(),
            'questions_open' => QuestionConversation::query()->open()->count(),
            'questions_total' => QuestionConversation::query()->count(),
            'publications' => Publication::query()->count(),
            'publications_published' => Publication::query()->published()->count(),
            'register_today' => $todaySheet
                ? $todaySheet->entries()->count()
                : 0,
            'register_pending_adhesion' => ActivityEntry::query()
                ->where('adhesion_done', false)
                ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', '>=', now()->subDays(14)))
                ->count(),
        ];

        $recentMessages = ContactMessage::query()
            ->latest()
            ->limit(5)
            ->get();

        $recentQuestions = QuestionConversation::query()
            ->with(['member:id,name,email', 'latestMessage'])
            ->latest('last_message_at')
            ->limit(5)
            ->get();

        return view('it-manager.dashboard', compact('stats', 'recentMessages', 'recentQuestions'));
    }
}
