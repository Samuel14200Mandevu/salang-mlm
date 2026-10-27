(function () {
    const form = document.getElementById('loginForm');
    if (!form) return;

    const loginInput = form.querySelector('#login') || form.querySelector('#email');
    const passwordInput = form.querySelector('#password');

    const setFieldError = window.salangAuthSetFieldError;

    form.addEventListener('submit', function (e) {
        window.salangAuthClearJsFieldErrors?.(form);

        const login = loginInput?.value.trim() ?? '';
        const password = passwordInput?.value ?? '';
        let blocked = false;

        if (!login) {
            e.preventDefault();
            setFieldError?.(loginInput, 'Indiquez votre email, ID membre ou téléphone.');
            loginInput?.focus();
            blocked = true;
        } else {
            setFieldError?.(loginInput, null);
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
