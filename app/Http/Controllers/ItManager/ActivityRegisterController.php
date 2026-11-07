<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\ActivityEntry;
use App\Models\ActivitySheet;
use App\Models\User;
use App\Services\ActivityRegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ActivityRegisterController extends Controller
{
    public function __construct(
        protected ActivityRegisterService $register
    ) {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function index(Request $request): View
    {
        // Ne garder que les jours qui ont réellement des participants
        ActivitySheet::query()->whereDoesntHave('entries')->delete();

        $today = now()->startOfDay();
        $todaySheet = ActivitySheet::findForDate($today);

        $sheets = ActivitySheet::query()
            ->withCount('entries')
            ->withCount([
                'entries as adhesion_count' => fn ($q) => $q->where('adhesion_done', true),
                'entries as consultation_count' => fn ($q) => $q->where('consultation_done', true),
                'entries as purchase_count' => fn ($q) => $q->where('purchase_done', true),
            ])
            ->has('entries')
            ->latest('activity_date')
            ->paginate(20);

        return view('it-manager.activity-register.index', compact('sheets', 'today', 'todaySheet'));
    }

    public function show(Request $request, ?string $date = null): View
    {
        $day = Carbon::parse($date ?: $request->string('date')->toString() ?: now()->toDateString());
        $sheet = ActivitySheet::findForDate($day) ?? ActivitySheet::draftForDate($day);

        if ($sheet->exists) {
            $sheet->load([
                'entries.member:id,name,sponsor_id',
                'entries.adhesionUser:id,name,sponsor_id',
                'entries.consultation:id,numero,nom_complet',
                'entries.order:id,total,status',
            ]);
        }

        return view('it-manager.activity-register.show', [
            'sheet' => $sheet,
            'day' => $day,
        ]);
    }

    public function lookupMember(Request $request): JsonResponse
    {
        $code = trim((string) $request->query('code', ''));

        if ($code === '') {
            return response()->json([
                'valid' => false,
                'message' => 'Saisissez un code membre.',
            ]);
        }

        $member = User::query()
            ->where('sponsor_id', $code)
            ->where('is_active', true)
            ->first();

        if (! $member) {
            return response()->json([
                'valid' => false,
                'message' => 'Aucun membre actif pour ce code.',
            ]);
        }

        [$nom, $postnom] = $this->splitNomPostnom($member->name);

        return response()->json([
            'valid' => true,
            'member' => [
                'id' => $member->id,
                'code' => $member->sponsor_id,
                'name' => $member->name,
                'nom' => $nom,
                'postnom' => $postnom,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_date' => ['required', 'date'],
            'member_code' => ['required', 'string', 'max:32'],
            'invite_type' => ['required', 'in:formation,autre'],
            'participant_nom' => ['required', 'string', 'max:120'],
            'participant_postnom' => ['required', 'string', 'max:120'],
            'participant_prenom' => ['required', 'string', 'max:120'],
            'participant_birth_date' => ['nullable', 'date'],
            'participant_gender' => ['nullable', 'in:male,female'],
            'participant_address' => ['nullable', 'string', 'max:500'],
            'participant_phone' => ['required', 'string', 'max:40'],
            'participant_email' => ['nullable', 'email', 'max:255'],
            'participant_profession' => ['nullable', 'string', 'max:255'],
            'participant_identity_number' => ['nullable', 'string', 'max:100'],
        ], [
            'member_code.required' => 'Le code membre (parrain) est obligatoire.',
            'participant_nom.required' => 'Le nom est obligatoire.',
            'participant_postnom.required' => 'Le post-nom est obligatoire.',
            'participant_prenom.required' => 'Le prénom est obligatoire.',
            'participant_phone.required' => 'Le téléphone de l’invité est obligatoire.',
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

        $entry = $this->register->addParticipant(
            date: $data['activity_date'],
            member: $member,
            data: [
                'nom' => $data['participant_nom'],
                'postnom' => $data['participant_postnom'],
                'prenom' => $data['participant_prenom'],
                'phone' => $data['participant_phone'],
                'birth_date' => $data['participant_birth_date'] ?? null,
                'gender' => $data['participant_gender'] ?? null,
                'address' => $data['participant_address'] ?? null,
                'email' => $data['participant_email'] ?? null,
                'profession' => $data['participant_profession'] ?? null,
                'identity_number' => $data['participant_identity_number'] ?? null,
                'invite_type' => $data['invite_type'],
            ],
            actor: $request->user(),
        );

        return redirect()
            ->route('it-manager.activity-register.show', ['date' => Carbon::parse($data['activity_date'])->toDateString()])
            ->with('success', 'Participant « '.$entry->displayName().' » ajouté pour '.$member->name.'.');
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitNomPostnom(?string $fullName): array
    {
        $parts = preg_split('/\s+/u', trim((string) $fullName), 2) ?: [];
        $nom = $parts[0] ?? '';
        $postnom = $parts[1] ?? '';

        return [$nom, $postnom];
    }

    public function update(Request $request, ActivityEntry $entry): RedirectResponse
    {
        $data = $request->validate([
            'formation_done' => ['sometimes', 'boolean'],
            'adhesion_done' => ['sometimes', 'boolean'],
            'consultation_done' => ['sometimes', 'boolean'],
            'purchase_done' => ['sometimes', 'boolean'],
            'purchases_amount' => ['nullable', 'numeric', 'min:0'],
            'net_sales_amount' => ['nullable', 'numeric', 'min:0'],
            'validated_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = [];

        foreach (['formation_done', 'adhesion_done', 'consultation_done', 'purchase_done'] as $flag) {
            if ($request->has($flag)) {
                $payload[$flag] = $request->boolean($flag);
            }
        }

        foreach (['purchases_amount', 'net_sales_amount', 'validated_amount', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (($payload['adhesion_done'] ?? null) === true && ! $entry->adhesion_at) {
            $payload['adhesion_at'] = now();
        }
        if (($payload['consultation_done'] ?? null) === true && ! $entry->consultation_at) {
            $payload['consultation_at'] = now();
        }
        if (($payload['purchase_done'] ?? null) === true && ! $entry->purchase_at) {
            $payload['purchase_at'] = now();
        }

        $entry->forceFill($payload)->save();

        return back()->with('success', 'Ligne mise à jour.');
    }

    public function destroy(ActivityEntry $entry): RedirectResponse
    {
        $date = $entry->sheet?->activity_date?->toDateString() ?? now()->toDateString();
        $entry->delete();

        return redirect()
            ->route('it-manager.activity-register.show', ['date' => $date])
            ->with('success', 'Ligne supprimée.');
    }
}
