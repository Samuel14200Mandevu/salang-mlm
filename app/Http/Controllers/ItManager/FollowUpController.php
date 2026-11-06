<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\ActivityEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString() ?: 'formation';
        if (! in_array($filter, ['formation', 'adhesion', 'consultation', 'purchase'], true)) {
            $filter = 'formation';
        }

        $memberCode = trim((string) $request->query('member_code', ''));

        $from = now()->subDays(30)->toDateString();
        $inWindow = fn ($q) => $q->whereHas('sheet', fn ($s) => $s->whereDate('activity_date', '>=', $from));
        $byMemberCode = function ($q) use ($memberCode) {
            if ($memberCode === '') {
                return;
            }

            $q->where(function ($inner) use ($memberCode) {
                $inner->where('member_code', 'like', "%{$memberCode}%")
                    ->orWhereHas('member', fn ($m) => $m->where('sponsor_id', 'like', "%{$memberCode}%"));
            });
        };

        $query = ActivityEntry::query()
            ->with(['member:id,name,sponsor_id', 'sheet:id,activity_date'])
            ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', '>=', $from))
            ->tap($byMemberCode)
            ->latest('id');

        match ($filter) {
            'formation' => $query->where('formation_done', true),
            'adhesion' => $query->where('adhesion_done', false),
            'consultation' => $query->where('consultation_done', false),
            'purchase' => $query->where('purchase_done', false),
        };

        $entries = $query->paginate(30)->withQueryString();

        $counts = [
            'formation' => ActivityEntry::query()
                ->where('formation_done', true)
                ->tap($inWindow)
                ->tap($byMemberCode)
                ->count(),
            'adhesion' => ActivityEntry::query()
                ->where('adhesion_done', false)
                ->tap($inWindow)
                ->tap($byMemberCode)
                ->count(),
            'consultation' => ActivityEntry::query()
                ->where('consultation_done', false)
                ->tap($inWindow)
                ->tap($byMemberCode)
                ->count(),
            'purchase' => ActivityEntry::query()
                ->where('purchase_done', false)
                ->tap($inWindow)
                ->tap($byMemberCode)
                ->count(),
        ];

        return view('it-manager.follow-up.index', compact('entries', 'filter', 'counts', 'memberCode'));
    }

    public function show(Request $request, ActivityEntry $entry): View
    {
        $filter = $request->string('filter')->toString() ?: 'formation';
        if (! in_array($filter, ['formation', 'adhesion', 'consultation', 'purchase'], true)) {
            $filter = 'formation';
        }

        $memberCode = trim((string) $request->query('member_code', ''));

        $entry->load(['member:id,name,sponsor_id', 'sheet:id,activity_date']);

        return view('it-manager.follow-up.show', compact('entry', 'filter', 'memberCode'));
    }
}
