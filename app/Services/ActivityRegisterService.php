<?php

namespace App\Services;

use App\Models\ActivityEntry;
use App\Models\ActivitySheet;
use App\Models\Consultation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ActivityRegisterService
{
    /**
     * Ajoute un participant détaillé sous un membre pour une date.
     *
     * @param  array{
     *     name?: string|null,
     *     nom?: string|null,
     *     postnom?: string|null,
     *     prenom?: string|null,
     *     phone: string,
     *     birth_date?: string|null,
     *     gender?: string|null,
     *     address?: string|null,
     *     email?: string|null,
     *     profession?: string|null,
     *     identity_number?: string|null,
     *     invite_type?: string,
     *     notes?: string|null
     * }  $data
     */
    public function addParticipant(
        Carbon|string $date,
        User $member,
        array $data,
        ?User $actor = null,
    ): ActivityEntry {
        $sheet = ActivitySheet::forDate($date, $actor);
        $inviteType = $data['invite_type'] ?? ActivityEntry::INVITE_FORMATION;

        $nextOrder = (int) $sheet->entries()
            ->where('member_id', $member->id)
            ->max('sort_order') + 1;

        $nom = trim((string) ($data['nom'] ?? ''));
        $postnom = trim((string) ($data['postnom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $fullName = ActivityEntry::composeFullName($nom, $postnom, $prenom);
        if ($fullName === '') {
            $fullName = trim((string) ($data['name'] ?? ''));
        }

        return ActivityEntry::query()->create([
            'activity_sheet_id' => $sheet->id,
            'member_id' => $member->id,
            'member_code' => $member->sponsor_id,
            'participant_name' => $fullName,
            'participant_nom' => $nom !== '' ? $nom : null,
            'participant_postnom' => $postnom !== '' ? $postnom : null,
            'participant_prenom' => $prenom !== '' ? $prenom : null,
            'participant_phone' => $data['phone'] ?? null,
            'participant_birth_date' => $data['birth_date'] ?? null,
            'participant_gender' => $data['gender'] ?? null,
            'participant_address' => $data['address'] ?? null,
            'participant_email' => $data['email'] ?? null,
            'participant_profession' => $data['profession'] ?? null,
            'participant_identity_number' => $data['identity_number'] ?? null,
            'sort_order' => $nextOrder,
            'invite_type' => $inviteType,
            'formation_done' => $inviteType === ActivityEntry::INVITE_FORMATION,
            'notes' => $data['notes'] ?? null,
            'created_by' => $actor?->id,
        ]);
    }

    /**
     * @param  list<string>|string  $participants
     * @return Collection<int, ActivityEntry>
     */
    public function addParticipants(
        Carbon|string $date,
        User $member,
        array|string $participants,
        ?User $actor = null,
        string $inviteType = ActivityEntry::INVITE_FORMATION,
        ?string $phone = null,
    ): Collection {
        $names = is_array($participants) ? $participants : [$participants];
        $created = collect();

        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $created->push($this->addParticipant(
                date: $date,
                member: $member,
                data: [
                    'name' => $name,
                    'phone' => $phone ?? '',
                    'invite_type' => $inviteType,
                ],
                actor: $actor,
            ));
        }

        return $created;
    }

    public function markAdhesion(User $newMember, ?Carbon $at = null, ?ActivityEntry $forcedEntry = null): ?ActivityEntry
    {
        $entry = $forcedEntry;

        if ($entry && $entry->adhesion_done) {
            return $entry;
        }

        if (! $entry) {
            $sponsorId = $newMember->parrain_id;
            if (! $sponsorId) {
                return null;
            }

            // Match téléphone d’abord (plus fiable), puis nom
            if ($newMember->phone) {
                $phone = preg_replace('/\D+/', '', (string) $newMember->phone);
                if ($phone !== '') {
                    $entry = ActivityEntry::query()
                        ->where('member_id', $sponsorId)
                        ->where('adhesion_done', false)
                        ->whereNotNull('participant_phone')
                        ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', '>=', now()->subDays(14)))
                        ->latest('id')
                        ->get()
                        ->first(function (ActivityEntry $candidate) use ($phone) {
                            $candidatePhone = preg_replace('/\D+/', '', (string) $candidate->participant_phone);

                            return $candidatePhone !== '' && (
                                str_contains($candidatePhone, $phone)
                                || str_contains($phone, $candidatePhone)
                            );
                        });
                }
            }

            if (! $entry) {
                $entry = $this->findBestMatch(
                    memberId: (int) $sponsorId,
                    participantName: $newMember->name,
                    require: fn ($q) => $q->where('adhesion_done', false),
                );
            }
        }

        if (! $entry) {
            return null;
        }

        $entry->forceFill([
            'adhesion_done' => true,
            'adhesion_user_id' => $newMember->id,
            'adhesion_at' => $at ?? now(),
        ])->save();

        return $entry;
    }

    /**
     * Invités du registre en attente d’adhésion (14 derniers jours).
     *
     * @return Collection<int, ActivityEntry>
     */
    public function searchPendingGuests(string $query, int $limit = 12): Collection
    {
        $q = trim($query);
        if (mb_strlen($q) < 2) {
            return collect();
        }

        $from = now()->subDays(14)->toDateString();
        $digits = preg_replace('/\D+/', '', $q);

        return ActivityEntry::query()
            ->with(['member:id,name,sponsor_id', 'sheet:id,activity_date'])
            ->where('adhesion_done', false)
            ->whereHas('sheet', fn ($sheet) => $sheet->whereDate('activity_date', '>=', $from))
            ->where(function ($builder) use ($q, $digits) {
                $builder->where('participant_name', 'like', "%{$q}%")
                    ->orWhere('participant_nom', 'like', "%{$q}%")
                    ->orWhere('participant_postnom', 'like', "%{$q}%")
                    ->orWhere('participant_prenom', 'like', "%{$q}%")
                    ->orWhere('participant_phone', 'like', "%{$q}%")
                    ->orWhere('member_code', 'like', "%{$q}%");

                if ($digits !== '') {
                    $builder->orWhere('participant_phone', 'like', "%{$digits}%");
                }
            })
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function markConsultation(Consultation $consultation, ?Carbon $at = null): ?ActivityEntry
    {
        $memberId = $consultation->client_id;
        $name = $consultation->nom_complet;
        if (! $memberId || ! $name) {
            return null;
        }

        $entry = $this->findBestMatch(
            memberId: (int) $memberId,
            participantName: $name,
            require: fn ($q) => $q->where('consultation_done', false),
        );

        if (! $entry) {
            return null;
        }

        $entry->forceFill([
            'consultation_done' => true,
            'consultation_id' => $consultation->id,
            'consultation_at' => $at ?? now(),
        ])->save();

        return $entry;
    }

    public function markPurchase(Order $order, ?Carbon $at = null): ?ActivityEntry
    {
        $userId = $order->user_id;
        if (! $userId) {
            return null;
        }

        $buyer = User::query()->find($userId);
        if (! $buyer) {
            return null;
        }

        // 1) Achat par un membre issu d'une adhésion liée au registre
        $entry = ActivityEntry::query()
            ->where('adhesion_user_id', $buyer->id)
            ->where('purchase_done', false)
            ->latest('id')
            ->first();

        // 2) Sinon match nom + parrain
        if (! $entry && $buyer->parrain_id) {
            $entry = $this->findBestMatch(
                memberId: (int) $buyer->parrain_id,
                participantName: $buyer->name,
                require: fn ($q) => $q->where('purchase_done', false),
            );
        }

        if (! $entry) {
            return null;
        }

        $amount = (float) ($order->total ?? $order->total_amount ?? 0);

        $entry->forceFill([
            'purchase_done' => true,
            'order_id' => $order->id,
            'purchases_amount' => $amount,
            'net_sales_amount' => $amount,
            'validated_amount' => $entry->validated_amount ?? $amount,
            'purchase_at' => $at ?? now(),
        ])->save();

        return $entry;
    }

    /**
     * @param  callable(Builder): mixed|null  $require
     */
    protected function findBestMatch(int $memberId, string $participantName, ?callable $require = null): ?ActivityEntry
    {
        $needle = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $participantName) ?? ''));
        if ($needle === '') {
            return null;
        }

        $from = now()->subDays(14)->toDateString();

        $query = ActivityEntry::query()
            ->where('member_id', $memberId)
            ->whereHas('sheet', fn ($q) => $q->whereDate('activity_date', '>=', $from))
            ->latest('id');

        if ($require) {
            $require($query);
        }

        /** @var Collection<int, ActivityEntry> $candidates */
        $candidates = $query->limit(40)->get();

        return $candidates->first(function (ActivityEntry $entry) use ($needle) {
            return $entry->normalizedParticipantName() === $needle;
        }) ?? $candidates->first(function (ActivityEntry $entry) use ($needle) {
            $hay = $entry->normalizedParticipantName();

            return $hay !== '' && (str_contains($hay, $needle) || str_contains($needle, $hay));
        });
    }
}
