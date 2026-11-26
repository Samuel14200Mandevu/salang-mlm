<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\ActivityEntry;
use App\Models\User;
use App\Services\ActivityRegisterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
