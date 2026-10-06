(function () {
    const form = document.getElementById('loginForm');
    if (!form) return;

    const emailInput = form.querySelector('#email');
    const passwordInput = form.querySelector('#password');
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    form.addEventListener('submit', function (e) {
        const email = emailInput?.value.trim() ?? '';
        const password = passwordInput?.value ?? '';
        if (!email || !emailRe.test(email)) {
            e.preventDefault();
            window.showToast?.('Saisissez une adresse email valide.', 'error');
            emailInput?.focus();
            return;
        }
        if (!password) {
            e.preventDefault();
            window.showToast?.('Mot de passe requis.', 'error');
            passwordInput?.focus();
        }
    });
})();
