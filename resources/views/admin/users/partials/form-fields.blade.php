@php
    /** @var \App\Models\User|null $user */
    $isEdit = isset($user) && $user;
    $val = function (string $field, $default = null) use ($user, $isEdit) {
        if ($isEdit) {
            return old($field, $user->{$field} ?? $default);
        }

        return old($field, $default);
    };
    $selectedRole = old('role', $isEdit ? ($user->roles->first()?->name ?? 'user') : 'user');
    $formId = $isEdit ? 'userEditForm' : 'adminUserCreateForm';
@endphp

<div class="user-form-layout">
    <div class="user-form-main space-y-4">
        <section class="user-form-section">
            <div class="user-form-section__head">
                <h3>Identité</h3>
                <p>Coordonnées du compte et accès.</p>
            </div>
            <div class="user-form-grid user-form-grid--2">
                <div class="form-group">
                    <label for="name">Nom complet <span class="required">*</span></label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ $val('name') }}"
                           class="form-control @error('name') form-control-error @enderror"
                           placeholder="Nom et prénom"
                           required>
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ $val('email') }}"
                           class="form-control @error('email') form-control-error @enderror"
                           placeholder="membre@exemple.com"
                           required>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">
                        {{ $isEdit ? 'Nouveau mot de passe' : 'Mot de passe' }}
                        @unless($isEdit)<span class="required">*</span>@endunless
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control @error('password') form-control-error @enderror"
                           placeholder="{{ $isEdit ? 'Laissez vide pour conserver' : '••••••••' }}"
                           @unless($isEdit) required @endunless>
                    <span class="help-text">Minimum 8 caractères</span>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        Confirmer le mot de passe
                        @unless($isEdit)<span class="required">*</span>@endunless
                    </label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-control"
                           placeholder="••••••••"
                           @unless($isEdit) required @endunless>
                </div>

                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="{{ $val('phone') }}"
                           class="form-control @error('phone') form-control-error @enderror"
                           placeholder="+243 …">
                    @error('phone')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="country">Pays</label>
                    <input type="text"
                           id="country"
                           name="country"
                           value="{{ $val('country') }}"
                           class="form-control"
                           placeholder="RDC">
                </div>

                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text"
                           id="city"
                           name="city"
                           value="{{ $val('city') }}"
                           class="form-control"
                           placeholder="Bukavu">
                </div>

                <div class="form-group user-form-grid__full">
                    <label for="address">Adresse</label>
                    <textarea id="address"
                              name="address"
                              rows="2"
                              class="form-control"
                              placeholder="Adresse complète…">{{ $val('address') }}</textarea>
                </div>
            </div>
        </section>

        <section class="user-form-section" id="mlmFieldsSection">
            <div class="user-form-section__head">
                <h3>Réseau &amp; package</h3>
                <p>Code membre, parrainage et offre associée.</p>
            </div>
            <div class="user-form-grid user-form-grid--2">
                <div class="form-group" id="memberCodeGroup">
                    <label for="{{ $isEdit ? 'sponsor_id' : 'memberCode' }}">Code membre</label>
                    <input type="text"
                           name="sponsor_id"
                           id="{{ $isEdit ? 'sponsor_id' : 'memberCode' }}"
                           value="{{ $val('sponsor_id') }}"
                           class="form-control font-mono @error('sponsor_id') form-control-error @enderror"
                           placeholder="Ex. 511671"
                           autocomplete="off"
                           maxlength="20">
                    <span class="help-text">
                        {{ $isEdit ? 'Code unique du compte (parrainage).' : 'Optionnel. Si vide, généré automatiquement.' }}
                    </span>
                    @error('sponsor_id')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group" id="packageGroup">
                    <label for="package_id">Package</label>
                    <select id="package_id" name="package_id" class="form-control">
                        <option value="">Aucun</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}"
                                @selected((string) $val('package_id', $isEdit ? $user->package_id : null) === (string) $package->id)>
                                {{ $package->name }} (${{ number_format($package->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group user-form-grid__full" id="sponsorGroup">
                    <label for="parrainCode">Sponsor (Parrain)</label>
                    <input type="text"
                           name="parrain_code"
                           id="parrainCode"
                           value="{{ old('parrain_code', $isEdit ? ($user->parrain?->sponsor_id) : null) }}"
                           class="form-control @error('parrain_code') form-control-error @enderror"
                           placeholder="Ex. 51234567"
                           autocomplete="off">
                    <span class="help-text">
                        Saisissez le <strong>code de parrain</strong>.
                        {{ $isEdit ? 'Laissez le code actuel pour ne pas modifier.' : 'Laissez vide si aucun parrain.' }}
                    </span>
                    <div id="parrainFeedback" class="parrain-feedback"></div>
                    @if($isEdit)
                        <div class="current-sponsor-box">
                            @if($user->parrain)
                                <strong>Parrain actuel :</strong>
                                {{ $user->parrain->name }}
                                (Code: <span class="font-mono">{{ $user->parrain->sponsor_id }}</span>)
                            @else
                                <strong>Parrain actuel :</strong> Aucun
                            @endif
                        </div>
                    @endif
                    @error('parrain_code')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                @if($isEdit)
                    <div class="form-group" id="rankGroup">
                        <label for="rank_id">Grade (Rang)</label>
                        <select id="rank_id" name="rank_id" class="form-control">
                            <option value="">— Recalcul automatique —</option>
                            @foreach($ranks ?? [] as $rank)
                                <option value="{{ $rank->id }}"
                                    @selected((string) old('rank_id', $user->rank_id) === (string) $rank->id)>
                                    {{ $rank->name }} (Niv. {{ $rank->level }})
                                </option>
                            @endforeach
                        </select>
                        <span class="help-text">
                            Actuel : <strong>{{ $user->rank ?? 'Distributeur' }}</strong>
                            (Niv. {{ $user->rank_level ?? 1 }})
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="created_at">Date d’adhésion</label>
                        <input type="datetime-local"
                               id="created_at"
                               name="created_at"
                               value="{{ old('created_at', optional($user->created_at)->format('Y-m-d\TH:i')) }}"
                               class="form-control @error('created_at') form-control-error @enderror">
                        @error('created_at')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
        </section>

        @if($isEdit)
            <section class="user-form-section space-y-3">
                <div class="warning-box">
                    <p class="font-semibold text-sm mb-1">Important — Grade</p>
                    <p class="text-sm">
                        Champ grade vide = recalcul auto selon les PV.
                        Sélectionnez un grade pour le forcer manuellement.
                    </p>
                </div>
                <div class="warning-box">
                    <p class="font-semibold text-sm mb-1">Important — Parrain</p>
                    <p class="text-sm">
                        Un changement de parrain impacte le réseau MLM (commissions, downline, grades).
                    </p>
                </div>
            </section>
        @endif
    </div>

    <aside class="user-form-side space-y-4">
        @if($isEdit)
            <section class="user-form-section">
                <div class="user-form-section__head">
                    <h3>Profil</h3>
                    <p>#{{ $user->id }} · {{ $user->email }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($user->avatar)
                        <img src="{{ asset('storage/avatars/'.$user->avatar) }}" alt="" class="user-avatar-preview">
                    @else
                        <div class="user-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-[var(--text-primary)] truncate">{{ $user->name }}</p>
                        <p class="text-xs text-[var(--text-tertiary)] font-mono truncate">{{ $user->sponsor_id ?: 'Sans code' }}</p>
                    </div>
                </div>
            </section>
        @endif

        <section class="user-form-section">
            <div class="user-form-section__head">
                <h3>Rôle &amp; statut</h3>
                <p>Droits d’accès et publication du compte.</p>
            </div>
            <div class="space-y-3">
                <div class="form-group">
                    @include('admin.users.partials.role-select', ['selectedRole' => $selectedRole])
                </div>

                <div class="form-group">
                    @php
                        $activeValue = (string) old(
                            'is_active',
                            $isEdit ? ($user->is_active ? '1' : '0') : '1'
                        );
                    @endphp
                    <label for="is_active">Statut</label>
                    <select id="is_active" name="is_active" class="form-control">
                        <option value="1" @selected($activeValue === '1')>Actif</option>
                        <option value="0" @selected($activeValue === '0')>Inactif</option>
                    </select>
                </div>

                @if($isEdit)
                    <div class="form-group">
                        <label for="kyc_status">Statut KYC</label>
                        <select id="kyc_status" name="kyc_status" class="form-control">
                            @foreach([
                                'not_submitted' => 'Non soumis',
                                'pending' => 'En attente',
                                'partial' => 'Partiel',
                                'verified' => 'Vérifié',
                                'rejected' => 'Rejeté',
                            ] as $kycValue => $kycLabel)
                                <option value="{{ $kycValue }}" @selected(old('kyc_status', $user->kyc_status) === $kycValue)>
                                    {{ $kycLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="info-box" id="roleInfoBox">
                    <p class="text-sm font-medium text-[var(--text-primary)] mb-0.5">Informations</p>
                    <p class="text-sm" id="roleInfoText">
                        @if($selectedRole === 'cashier')
                            Employé POS : pas de réseau MLM ni de commissions.
                        @elseif($selectedRole === 'admin')
                            Accès administration complet.
                        @elseif($selectedRole === 'it_manager')
                            Supervision (assistance, publications).
                        @else
                            Compte membre MLM avec code et commissions.
                        @endif
                    </p>
                </div>
            </div>
        </section>

        <div class="user-form-actions">
            <button type="submit" class="btn btn-primary w-full" form="{{ $formId }}">
                {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le compte' }}
            </button>
            <a href="{{ $isEdit ? route('admin.users.show', $user->id) : route('admin.users') }}"
               class="btn btn-outline w-full">
                Annuler
            </a>
        </div>
    </aside>
</div>
