/** Indicateur de robustesse mot de passe (register, reset). */
(function () {
    function bindMeter(form) {
        const passwordInput = form.querySelector('[data-password-meter]');
        const bar = form.querySelector('[data-password-meter-bar]');
        const hint = form.querySelector('[data-password-meter-label]');
        if (!passwordInput || !bar || !hint) {
            return;
        }

        passwordInput.addEventListener('input', function () {
            const value = this.value;
            let score = 0;
            if (value.length >= 8) score += 25;
            if (/[a-z]/.test(value)) score += 25;
            if (/[A-Z]/.test(value)) score += 25;
            if (/[0-9]/.test(value)) score += 25;

            bar.style.width = score + '%';
            if (score <= 25) {
                bar.style.background = 'var(--danger)';
            } else if (score <= 50) {
                bar.style.background = 'var(--warning)';
            } else {
                bar.style.background = 'var(--success)';
            }

            if (!value.length) {
                hint.textContent = hint.dataset.emptyLabel || '8 caractères minimum';
                return;
            }
            const labels = ['Faible', 'Correcte', 'Élevée', 'Élevée'];
            const idx = Math.min(Math.floor(score / 25) - 1, 2);
            hint.textContent = 'Robustesse : ' + (labels[idx] ?? 'Correcte');
        });
    }

    document.querySelectorAll('form[data-password-form]').forEach(bindMeter);
})();
