/**
 * Comportements communs — pages auth (mot de passe, soumission).
 */
(function () {
    if (!document.body.classList.contains('auth-page')) {
        return;
    }

    document.addEventListener('click', function (e) {
        const toggle = e.target.closest('.password-toggle');
        if (!toggle) {
            return;
        }
        const wrapper = toggle.closest('.password-wrapper');
        const input = wrapper?.querySelector('input');
        if (!input) {
            return;
        }
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
    });

    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) {
            return;
        }
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || form.classList.contains('auth-form-skip')) {
            return;
        }
        const btn = form.querySelector('button[type="submit"].auth-btn-primary:not([data-no-loading])');
        if (!btn || btn.disabled) {
            return;
        }
        btn.disabled = true;
        btn.dataset.loading = '1';
        const label = btn.dataset.loadingText || 'Patientez…';
        if (!btn.dataset.originalHtml) {
            btn.dataset.originalHtml = btn.innerHTML;
        }
        btn.innerHTML = '<span class="auth-spinner inline-block align-middle mr-2"></span>' + label;
    });
})();
