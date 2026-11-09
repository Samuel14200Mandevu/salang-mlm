@php
    $isEdit = isset($user) && $user;
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('userRoleSelect');
    const roleInfoText = document.getElementById('roleInfoText');
    const packageGroup = document.getElementById('packageGroup');
    const sponsorGroup = document.getElementById('sponsorGroup');
    const memberCodeGroup = document.getElementById('memberCodeGroup');
    const rankGroup = document.getElementById('rankGroup');
    const mlmSection = document.getElementById('mlmFieldsSection');

    const infoMessages = {
        user: 'Compte membre MLM : code, parrainage et commissions disponibles.',
        cashier: 'Employé POS : pas de code membre, pas de commissions, pas de réseau MLM.',
        admin: 'Administrateur avec accès complet. Un code membre peut être saisi ou généré.',
        it_manager: 'Responsable IT : assistance et publications. Compte staff (pas de réseau MLM).'
    };

    function updateRoleDisplay(role) {
        if (roleInfoText) {
            roleInfoText.textContent = infoMessages[role] || infoMessages.user;
        }

        const hideMlm = role === 'cashier' || role === 'it_manager';
        [packageGroup, sponsorGroup, memberCodeGroup, rankGroup].forEach(function (el) {
            if (el) el.style.display = hideMlm ? 'none' : '';
        });
        if (mlmSection) {
            mlmSection.style.display = hideMlm ? 'none' : '';
        }
    }

    if (roleSelect) {
        updateRoleDisplay(roleSelect.value);
        roleSelect.addEventListener('change', function () {
            updateRoleDisplay(roleSelect.value);
        });
    }

    const parrainInput = document.getElementById('parrainCode');
    const parrainFeedback = document.getElementById('parrainFeedback');
    const form = document.getElementById(@json($isEdit ? 'userEditForm' : 'adminUserCreateForm'));
    @if($isEdit)
    const currentUserId = {{ $user->id }};
    const currentParrainId = {{ $user->parrain_id ?? 'null' }};
    @endif
    let parrainValide = false;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function verifierCodeParrain(code) {
        if (!parrainFeedback) return;
        parrainFeedback.className = 'parrain-feedback loading';
        parrainFeedback.innerHTML = 'Vérification du code…';

        fetch('{{ route('admin.users.verify-sponsor') }}?code=' + encodeURIComponent(code), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(function (data) {
            if (!data.valid) {
                parrainFeedback.className = 'parrain-feedback error';
                parrainFeedback.innerHTML = '✗ ' + escapeHtml(data.message || 'Code invalide');
                return;
            }

            @if($isEdit)
            if (data.id === currentUserId) {
                parrainFeedback.className = 'parrain-feedback error';
                parrainFeedback.innerHTML = '✗ Un utilisateur ne peut pas être son propre parrain.';
                return;
            }
            if (data.id === currentParrainId) {
                parrainValide = true;
                parrainFeedback.className = 'parrain-feedback loading';
                parrainFeedback.innerHTML = 'ℹ Parrain identique au parrain actuel.';
                return;
            }
            @endif

            parrainValide = true;
            parrainFeedback.className = 'parrain-feedback success';
            parrainFeedback.innerHTML = '✓ Parrain trouvé : <strong>' +
                escapeHtml(data.name) + '</strong> (Code: ' + escapeHtml(data.sponsor_id) + ')';
        })
        .catch(function () {
            parrainFeedback.className = 'parrain-feedback error';
            parrainFeedback.innerHTML = '✗ Erreur de vérification. Réessayez.';
        });
    }

    if (parrainInput) {
        let debounceTimer;
        parrainInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const code = this.value.trim();
            parrainValide = false;
            if (!code) {
                if (parrainFeedback) {
                    parrainFeedback.className = 'parrain-feedback';
                    parrainFeedback.innerHTML = '';
                }
                return;
            }
            debounceTimer = setTimeout(function () {
                verifierCodeParrain(code);
            }, 500);
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            const code = parrainInput ? parrainInput.value.trim() : '';
            if (code !== '' && !parrainValide) {
                e.preventDefault();
                if (parrainFeedback) {
                    parrainFeedback.className = 'parrain-feedback error';
                    parrainFeedback.innerHTML = '✗ Attendez la vérification du code parrain ou corrigez-le.';
                }
                if (parrainInput) {
                    parrainInput.focus();
                    parrainInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
});
</script>
