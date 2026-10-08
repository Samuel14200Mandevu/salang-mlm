/**
 * Comportements communs — pages auth (mot de passe, soumission).
 */
(function () {
    if (!document.body.classList.contains('auth-page')) {
        return;
    }

    const JS_ERROR_CLASS = 'auth-field-error-js';

    function findGroup(el) {
        if (!el) {
            return null;
        }
        return el.closest('.auth-form-group') || el.closest('form');
    }

    function setFieldError(input, message) {
        if (!input) {
            return;
        }
        const group = findGroup(input);
        if (!group) {
            return;
        }

        let err = group.querySelector('.' + JS_ERROR_CLASS);
        if (message) {
            input.classList.add('auth-input-error');
            input.setAttribute('aria-invalid', 'true');
            if (!err) {
                err = document.createElement('p');
                err.className = 'auth-error ' + JS_ERROR_CLASS;
                err.setAttribute('role', 'alert');
                if (input.type === 'checkbox') {
                    group.appendChild(err);
                } else {
                    const anchor = input.closest('.password-wrapper') || input;
                    anchor.insertAdjacentElement('afterend', err);
                }
            }
            err.textContent = message;
            err.hidden = false;
        } else {
            input.classList.remove('auth-input-error');
            input.removeAttribute('aria-invalid');
            if (err) {
                err.remove();
            }
        }
    }

    function clearJsFieldErrors(form) {
        if (!form) {
            return;
        }
        form.querySelectorAll('.' + JS_ERROR_CLASS).forEach(function (el) {
            el.remove();
        });
        form.querySelectorAll('[aria-invalid="true"]').forEach(function (input) {
            if (!input.classList.contains('auth-input-error') || input.closest('.auth-form-group')) {
                const hasServerError = input.closest('.auth-form-group')?.querySelector('.auth-error:not(.' + JS_ERROR_CLASS + ')');
                if (!hasServerError) {
                    input.classList.remove('auth-input-error');
                    input.removeAttribute('aria-invalid');
                }
            }
        });
    }

    window.salangAuthSetFieldError = setFieldError;
    window.salangAuthClearJsFieldErrors = clearJsFieldErrors;

    document.addEventListener('input', function (e) {
        const input = e.target;
        if (!(input instanceof HTMLInputElement) && !(input instanceof HTMLTextAreaElement) && !(input instanceof HTMLSelectElement)) {
            return;
        }
        if (!input.classList.contains('auth-input') && !input.closest('.auth-form')) {
            return;
        }
        const group = findGroup(input);
        const jsErr = group?.querySelector('.' + JS_ERROR_CLASS);
        if (jsErr) {
            setFieldError(input, null);
        }
    });

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
