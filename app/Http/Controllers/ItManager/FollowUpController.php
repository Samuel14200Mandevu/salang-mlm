<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Http\Controllers\Controller;
use App\Models\ActivityEntry;
use App\Models\ActivitySheet;
use App\Models\User;
use App\Services\ActivityRegisterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FollowUpController extends Controller
{
    use GeneratesPdfSafely;

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

        $matchingMemberIds = (clone $matchingEntryIds)
            ->select('activity_entries.member_id')
            ->whereNotNull('activity_entries.member_id')
            ->groupBy('activity_entries.member_id');

        $groups = ActivityEntry::query()
            ->join('activity_sheets', 'activity_sheets.id', '=', 'activity_entries.activity_sheet_id')
            ->whereDate('activity_sheets.activity_date', '>=', $from)
            ->whereIn('activity_entries.member_id', $matchingMemberIds)
            ->select([
                'activity_entries.member_id',
                DB::raw('MAX(activity_entries.member_code) as member_code'),
                DB::raw('MAX(DATE(activity_sheets.activity_date)) as latest_activity_date'),
                DB::raw('COUNT(*) as invites_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.formation_done THEN 1 ELSE 0 END), 0) as formation_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.adhesion_done THEN 1 ELSE 0 END), 0) as adhesion_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.consultation_done THEN 1 ELSE 0 END), 0) as consultation_count'),
                DB::raw('COALESCE(SUM(CASE WHEN activity_entries.purchase_done THEN 1 ELSE 0 END), 0) as purchase_count'),
            ])
            ->groupBy('activity_entries.member_id')
            ->orderByDesc('latest_activity_date')
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

    /**
     * Rapport global PDF — aperçu (Retour / Télécharger), puis flux DomPDF.
     */
    public function exportGlobalPdf(Request $request): BinaryFileResponse|Response|RedirectResponse|View
    {
        $memberCode = trim((string) $request->query('member_code', ''));
        $queryParams = array_filter([
            'member_code' => $memberCode !== '' ? $memberCode : null,
        ]);

        if (! $request->boolean('pdf')) {
            $exists = ActivityEntry::query()
                ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', '>=', now()->subDays(30)->toDateString()))
                ->whereNotNull('member_id')
                ->when($memberCode !== '', function ($q) use ($memberCode) {
                    $q->where(function ($inner) use ($memberCode) {
                        $inner->where('member_code', 'like', "%{$memberCode}%")
                            ->orWhereHas('member', fn ($m) => $m->where('sponsor_id', 'like', "%{$memberCode}%"));
                    });
                })
                ->exists();

            if (! $exists) {
                return back()->with('error', 'Aucun invité à exporter pour cette période.');
            }

            return view('partials.pdf.preview-shell', $this->pdfPreviewShellData(
                'Rapport global — Suivi registre',
                route('it-manager.follow-up.index', $queryParams),
                $request->url(),
                $queryParams,
            ));
        }

        $from = now()->subDays(30)->startOfDay();
        $to = now()->endOfDay();

        $query = ActivityEntry::query()
            ->with(['member:id,name,sponsor_id', 'sheet:id,activity_date'])
            ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', '>=', $from->toDateString()))
            ->whereNotNull('member_id');

        if ($memberCode !== '') {
            $query->where(function ($inner) use ($memberCode) {
                $inner->where('member_code', 'like', "%{$memberCode}%")
                    ->orWhereHas('member', fn ($m) => $m->where('sponsor_id', 'like', "%{$memberCode}%"));
            });
        }

        $entries = $query->orderBy('member_id')->orderBy('id')->get();

        if ($entries->isEmpty()) {
            return back()->with('error', 'Aucun invité à exporter pour cette période.');
        }

        $rows = $entries->groupBy('member_id')->map(function ($items) {
            $member = $items->first()->member;

            return [
                'name' => $member?->name ?: '—',
                'code' => $member?->sponsor_id ?: ($items->first()->member_code ?: '—'),
                'invites' => $items->count(),
                'formation' => $items->where('formation_done', true)->count(),
                'adhesion' => $items->where('adhesion_done', true)->count(),
                'consultation' => $items->where('consultation_done', true)->count(),
                'purchase' => $items->where('purchase_done', true)->count(),
            ];
        })->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE)->values();

        $totals = [
            'members' => $rows->count(),
            'invites' => (int) $rows->sum('invites'),
            'formation' => (int) $rows->sum('formation'),
            'adhesion' => (int) $rows->sum('adhesion'),
            'consultation' => (int) $rows->sum('consultation'),
            'purchase' => (int) $rows->sum('purchase'),
        ];

        $filename = 'rapport_global_suivi_registre_'.now()->format('Ymd_His').'.pdf';

        return $this->streamOrDownloadPdfView(
            $request->boolean('download'),
            'it-manager.follow-up.global-pdf',
            [
                'rows' => $rows,
                'totals' => $totals,
                'from' => $from,
                'to' => $to,
                'memberCode' => $memberCode,
                'generatedAt' => now(),
                'logoBase64' => $this->loadPdfLogoBase64(),
                'letterheadBase64' => $this->loadPdfLetterheadBase64(),
            ],
            $filename,
            'a4',
            'landscape',
        );
    }

    public function show(Request $request, User $member): View
    {
        $filter = $request->string('filter')->toString() ?: 'formation';
        if (! in_array($filter, ['formation', 'adhesion', 'consultation', 'purchase', 'sans_adhesion'], true)) {
            $filter = 'formation';
        }

        $memberCode = trim((string) $request->query('member_code', ''));

        $allEntries = ActivityEntry::query()
            ->with(['sheet:id,activity_date'])
            ->where('member_id', $member->id)
            ->whereHas('sheet')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->sortByDesc(fn (ActivityEntry $entry) => optional($entry->sheet)->activity_date
                ? Carbon::parse($entry->sheet->activity_date)->timestamp
                : 0)
            ->values();

        $entriesByDate = $allEntries
            ->groupBy(fn (ActivityEntry $entry) => optional($entry->sheet)->activity_date
                ? Carbon::parse($entry->sheet->activity_date)->toDateString()
                : 'unknown')
            ->map(fn ($entries) => $entries->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])->values());

        $memberTotals = [
            'invites' => $allEntries->count(),
            'formation' => $allEntries->where('formation_done', true)->count(),
            'adhesion' => $allEntries->where('adhesion_done', true)->count(),
            'consultation' => $allEntries->where('consultation_done', true)->count(),
            'purchase' => $allEntries->where('purchase_done', true)->count(),
        ];

        return view('it-manager.follow-up.show', [
            'member' => $member,
            'entriesByDate' => $entriesByDate,
            'memberTotals' => $memberTotals,
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
