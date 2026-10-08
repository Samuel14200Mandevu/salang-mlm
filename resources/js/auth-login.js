(function () {
    const form = document.getElementById('loginForm');
    if (!form) return;

    const emailInput = form.querySelector('#email');
    const passwordInput = form.querySelector('#password');
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const setFieldError = window.salangAuthSetFieldError;

    form.addEventListener('submit', function (e) {
        window.salangAuthClearJsFieldErrors?.(form);

        const email = emailInput?.value.trim() ?? '';
        const password = passwordInput?.value ?? '';
        let blocked = false;

        if (!email || !emailRe.test(email)) {
            e.preventDefault();
            setFieldError?.(emailInput, 'Saisissez une adresse email valide.');
            emailInput?.focus();
            blocked = true;
        } else {
            setFieldError?.(emailInput, null);
        }

        if (!password) {
            e.preventDefault();
            setFieldError?.(passwordInput, 'Mot de passe requis.');
            if (!blocked) {
                passwordInput?.focus();
            }
            blocked = true;
        } else if (!blocked) {
            setFieldError?.(passwordInput, null);
        }

    });
})();
