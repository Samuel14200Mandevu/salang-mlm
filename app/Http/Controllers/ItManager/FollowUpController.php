<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\ActivityEntry;
use App\Models\ActivitySheet;
use App\Models\User;
use App\Services\ActivityRegisterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function __construct(
        protected ActivityRegisterService $register
    ) {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString() ?: 'formation';
        if (! in_array($filter, ['formation', 'adhesion', 'consultation', 'purchase', 'sans_adhesion'], true)) {
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

        $matchingEntryIds = ActivityEntry::query()
            ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', '>=', $from))
            ->tap($byMemberCode);

        match ($filter) {
            'formation' => $matchingEntryIds->where('formation_done', true),
            'adhesion' => $matchingEntryIds->where('adhesion_done', true),
            'consultation' => $matchingEntryIds->where('consultation_done', true),
            'purchase' => $matchingEntryIds->where('purchase_done', true),
            'sans_adhesion' => $matchingEntryIds->where('adhesion_done', false),
        };

        $matchingPairs = (clone $matchingEntryIds)
            ->join('activity_sheets', 'activity_sheets.id', '=', 'activity_entries.activity_sheet_id')
            ->select('activity_entries.member_id', DB::raw('DATE(activity_sheets.activity_date) as activity_date'))
            ->groupBy('activity_entries.member_id', DB::raw('DATE(activity_sheets.activity_date)'));

        $groups = ActivityEntry::query()
            ->join('activity_sheets', 'activity_sheets.id', '=', 'activity_entries.activity_sheet_id')
            ->joinSub($matchingPairs, 'matched', function ($join) {
                $join->on('matched.member_id', '=', 'activity_entries.member_id')
                    ->whereRaw('DATE(activity_sheets.activity_date) = matched.activity_date');
            })
            ->select([
                'activity_entries.member_id',
                DB::raw('MAX(activity_entries.member_code) as member_code'),
                DB::raw('DATE(activity_sheets.activity_date) as activity_date'),
                DB::raw('COUNT(*) as invites_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.formation_done THEN 1 ELSE 0 END), 0) as formation_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.adhesion_done THEN 1 ELSE 0 END), 0) as adhesion_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.consultation_done THEN 1 ELSE 0 END), 0) as consultation_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.purchase_done THEN 1 ELSE 0 END), 0) as purchase_count'),
            ])
            ->groupBy('activity_entries.member_id', DB::raw('DATE(activity_sheets.activity_date)'))
            ->orderByDesc('activity_date')
            ->orderBy('activity_entries.member_id')
            ->paginate(30)
            ->withQueryString();

        $memberIds = collect($groups->items())->pluck('member_id')->unique()->filter()->values();
        $members = User::query()
            ->whereIn('id', $memberIds)
            ->get(['id', 'name', 'sponsor_id'])
            ->keyBy('id');

        $counts = [
            'formation' => ActivityEntry::query()
                ->where('formation_done', true)
                ->tap($inWindow)
                ->tap($byMemberCode)
                ->count(),
            'adhesion' => ActivityEntry::query()
                ->where('adhesion_done', true)
                ->tap($inWindow)
                ->tap($byMemberCode)
                ->count(),
            'consultation' => ActivityEntry::query()
                ->where('consultation_done', true)
                ->tap($inWindow)
                ->tap($byMemberCode)
                ->count(),
            'purchase' => ActivityEntry::query()
                ->where('purchase_done', true)
                ->tap($inWindow)
                ->tap($byMemberCode)
                ->count(),
        ];

        return view('it-manager.follow-up.index', [
            'groups' => $groups,
            'members' => $members,
            'filter' => $filter,
            'counts' => $counts,
            'memberCode' => $memberCode,
        ]);
    }

    public function show(Request $request, User $member): View
    {
        $filter = $request->string('filter')->toString() ?: 'formation';
        if (! in_array($filter, ['formation', 'adhesion', 'consultation', 'purchase', 'sans_adhesion'], true)) {
            $filter = 'formation';
        }

        $memberCode = trim((string) $request->query('member_code', ''));
        $focusDate = $request->query('date');
        $focusDay = $focusDate ? Carbon::parse($focusDate)->toDateString() : null;

        $dateStats = ActivityEntry::query()
            ->join('activity_sheets', 'activity_sheets.id', '=', 'activity_entries.activity_sheet_id')
            ->where('activity_entries.member_id', $member->id)
            ->select([
                DB::raw('DATE(activity_sheets.activity_date) as activity_date'),
                DB::raw('COUNT(*) as invites_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.formation_done THEN 1 ELSE 0 END), 0) as formation_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.adhesion_done THEN 1 ELSE 0 END), 0) as adhesion_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.consultation_done THEN 1 ELSE 0 END), 0) as consultation_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.purchase_done THEN 1 ELSE 0 END), 0) as purchase_count'),
            ])
            ->groupBy(DB::raw('DATE(activity_sheets.activity_date)'))
            ->orderByDesc('activity_date')
            ->get();

        if (! $focusDay && $dateStats->isNotEmpty()) {
            $focusDay = (string) $dateStats->first()->activity_date;
        }

        $dayEntries = collect();
        if ($focusDay) {
            $dayEntries = ActivityEntry::query()
                ->with(['sheet:id,activity_date'])
                ->where('member_id', $member->id)
                ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', $focusDay))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        return view('it-manager.follow-up.show', [
            'member' => $member,
            'dateStats' => $dateStats,
            'dayEntries' => $dayEntries,
            'focusDay' => $focusDay,
            'filter' => $filter,
            'memberCode' => $memberCode,
        ]);
    }

    /**
     * Déplace tous les invités du membre d’une date vers une autre.
     */
    public function updateDate(Request $request, User $member): RedirectResponse
    {
        $data = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date'],
            'filter' => ['nullable', 'string', 'max:32'],
            'member_code' => ['nullable', 'string', 'max:32'],
        ], [
            'from_date.required' => 'La date actuelle est obligatoire.',
            'to_date.required' => 'La nouvelle date est obligatoire.',
        ]);

        $fromDay = Carbon::parse($data['from_date'])->toDateString();
        $toDay = Carbon::parse($data['to_date'])->toDateString();

        if ($fromDay === $toDay) {
            return redirect()
                ->route('it-manager.follow-up.show', array_filter([
                    'member' => $member,
                    'date' => $fromDay,
                    'filter' => $data['filter'] ?? null,
                    'member_code' => ($data['member_code'] ?? null) ?: null,
                ]))
                ->with('success', 'Date inchangée.');
        }

        $entries = ActivityEntry::query()
            ->where('member_id', $member->id)
            ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', $fromDay))
            ->get();

        if ($entries->isEmpty()) {
            return back()->withErrors(['to_date' => 'Aucun invité à déplacer pour cette date.']);
        }

        $targetSheet = ActivitySheet::forDate($toDay, $request->user());
        $nextOrder = (int) $targetSheet->entries()
            ->where('member_id', $member->id)
            ->max('sort_order');

        foreach ($entries as $entry) {
            $nextOrder++;
            $entry->forceFill([
                'activity_sheet_id' => $targetSheet->id,
                'sort_order' => $nextOrder,
            ])->save();
        }

        return redirect()
            ->route('it-manager.follow-up.show', array_filter([
                'member' => $member,
                'date' => $toDay,
                'filter' => $data['filter'] ?? null,
                'member_code' => ($data['member_code'] ?? null) ?: null,
            ]))
            ->with('success', $entries->count().' invité(s) déplacé(s) au '.Carbon::parse($toDay)->format('d/m/Y').'.');
    }

    public function editEntry(Request $request, ActivityEntry $entry): View|RedirectResponse
    {
        $entry->load(['member:id,name,sponsor_id', 'sheet:id,activity_date']);

        if (! $entry->member_id) {
            return redirect()
                ->route('it-manager.follow-up.index')
                ->withErrors(['entry' => 'Invité sans membre associé.']);
        }

        $filter = $request->string('filter')->toString() ?: 'formation';
        $memberCode = trim((string) $request->query('member_code', ''));

        return view('it-manager.follow-up.edit-entry', [
            'entry' => $entry,
            'member' => $entry->member,
            'filter' => $filter,
            'memberCode' => $memberCode,
        ]);
    }

    /**
     * Ajoute un ou plusieurs invités (nom + post-nom) pour la date choisie.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_date' => ['required', 'date'],
            'member_code' => ['required', 'string', 'max:32'],
            'invite_type' => ['required', 'in:formation,autre'],
            'guests' => ['required', 'array', 'min:1'],
            'guests.*.nom' => ['required', 'string', 'max:120'],
            'guests.*.postnom' => ['required', 'string', 'max:120'],
        ], [
            'activity_date.required' => 'La date est obligatoire.',
            'member_code.required' => 'Le code membre (parrain) est obligatoire.',
            'guests.required' => 'Ajoutez au moins un invité.',
            'guests.*.nom.required' => 'Le nom est obligatoire.',
            'guests.*.postnom.required' => 'Le post-nom est obligatoire.',
        ]);

        $member = User::query()
            ->where('sponsor_id', trim($data['member_code']))
            ->where('is_active', true)
            ->first();

        if (! $member) {
            return back()->withInput()->withErrors([
                'member_code' => 'Aucun membre actif trouvé pour ce code.',
            ]);
        }

        $created = 0;
        $names = [];
        $day = Carbon::parse($data['activity_date'])->startOfDay();

        foreach ($data['guests'] as $guest) {
            $nom = trim((string) ($guest['nom'] ?? ''));
            $postnom = trim((string) ($guest['postnom'] ?? ''));
            if ($nom === '' || $postnom === '') {
                continue;
            }

            $entry = $this->register->addParticipant(
                date: $day,
                member: $member,
                data: [
                    'nom' => $nom,
                    'postnom' => $postnom,
                    'invite_type' => $data['invite_type'],
                ],
                actor: $request->user(),
            );
            $created++;
            $names[] = $entry->displayName();
        }

        if ($created === 0) {
            return back()->withInput()->withErrors([
                'guests' => 'Indiquez au moins un invité avec nom et post-nom.',
            ]);
        }

        $label = $created === 1
            ? 'Invité « '.$names[0].' » ajouté'
            : $created.' invités ajoutés ('.implode(', ', $names).')';

        return redirect()
            ->route('it-manager.follow-up.index', ['filter' => 'formation'])
            ->with('success', $label.' pour '.$member->name.' · '.$day->format('d/m/Y').'.');
    }
}
