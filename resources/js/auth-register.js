(function () {
    const form = document.getElementById('registerForm');
    if (!form) return;

    const checkSponsorUrl = form.dataset.checkSponsor || '/check-sponsor';
    const checkEmailUrl = form.dataset.checkEmail || '/check-email';
    const googleUrl = form.dataset.googleRedirect || '';

    const sponsorInput = form.querySelector('#sponsor_id');
    const sponsorStatus = form.querySelector('#sponsorStatus');
    const emailInput = form.querySelector('#email');
    const emailAvailability = form.querySelector('#emailAvailability');
    const googleBtn = document.getElementById('googleBtn');

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const setFieldError = window.salangAuthSetFieldError;
    let sponsorTimeout = null;
    let emailTimeout = null;
    let emailChecked = false;

    function setSponsorStatus(className, html) {
        if (!sponsorStatus) return;
        sponsorStatus.className = className;
        sponsorStatus.innerHTML = html;
    }

    if (sponsorInput && sponsorStatus) {
        sponsorInput.addEventListener('input', function () {
            const value = this.value.trim();
            this.classList.remove('auth-input-success', 'auth-input-error');
            setSponsorStatus('sponsor-status', '');

            if (value.length < 3) return;

            setSponsorStatus(
                'sponsor-status visible loading',
                '<span class="auth-spinner"></span> Vérification du parrain…'
            );

            clearTimeout(sponsorTimeout);
            sponsorTimeout = setTimeout(function () {
                fetch(checkSponsorUrl + '?sponsor_id=' + encodeURIComponent(value))
                    .then((r) => r.json())
                    .then((data) => {
                        if (data.exists) {
                            setSponsorStatus(
                                'sponsor-status visible success',
                                'Parrain confirmé : <strong></strong>'
                            );
                            const strong = sponsorStatus.querySelector('strong');
                            if (strong) strong.textContent = data.name + ' (' + data.email + ')';
                            sponsorInput.classList.add('auth-input-success');
                        } else {
                            setSponsorStatus('sponsor-status visible error', data.message || 'Code invalide.');
                            sponsorInput.classList.add('auth-input-error');
                        }
                    })
                    .catch(() => setSponsorStatus('sponsor-status', ''));
            }, 500);
        });
    }

    if (emailInput && emailAvailability) {
        emailInput.addEventListener('input', function () {
            const email = this.value.trim();
            emailChecked = false;
            this.classList.remove('auth-input-error', 'auth-input-success');
            emailAvailability.innerHTML = '';

            if (!emailRe.test(email)) return;

            emailAvailability.innerHTML =
                '<div class="email-checking"><span class="email-checking-spinner"></span>Vérification de l’email…</div>';

            clearTimeout(emailTimeout);
            emailTimeout = setTimeout(function () {
                fetch(checkEmailUrl + '?email=' + encodeURIComponent(email))
                    .then((r) => r.json())
                    .then((data) => {
                        emailChecked = true;
                        const status = data.field_status;
                        if (status === 'success') {
                            emailInput.classList.add('auth-input-success');
                            emailAvailability.innerHTML =
                                '<div class="email-status-success">' + (data.message || 'Email disponible') + '</div>';
                        } else if (status === 'error') {
                            emailInput.classList.add('auth-input-error');
                            emailAvailability.innerHTML =
                                '<div class="email-status-error">' + (data.message || 'Email indisponible') + '</div>';
                        } else {
                            emailInput.classList.add('auth-input-error');
                            emailAvailability.innerHTML =
                                '<div class="email-status-warning">' + (data.message || 'Vérifiez cet email') + '</div>';
                        }
                    })
                    .catch(() => {
                        emailAvailability.innerHTML = '';
                    });
            }, 500);
        });
    }

    if (googleBtn && googleUrl) {
        googleBtn.addEventListener('click', function () {
            const sponsorId = sponsorInput?.value.trim() ?? '';
            if (!sponsorId) {
                setFieldError?.(sponsorInput, 'Indiquez d’abord votre code parrain.');
                sponsorInput?.focus();
                return;
            }
            if (sponsorInput?.classList.contains('auth-input-error')) {
                setFieldError?.(sponsorInput, 'Code parrain invalide.');
                sponsorInput?.focus();
                return;
            }
            window.location.href = googleUrl + '?sponsor_id=' + encodeURIComponent(sponsorId);
        });
    }

    const nameInput = form.querySelector('#name');
    const passwordInput = form.querySelector('#password');
    const confirmInput = form.querySelector('#password_confirmation');
    const termsInput = form.querySelector('#terms');

    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            const email = this.value.trim();
            if (!email) {
                return;
            }
            if (!emailRe.test(email)) {
                setFieldError?.(emailInput, 'Format d’email invalide.');
            }
        });
    }

    if (passwordInput && confirmInput) {
        confirmInput.addEventListener('blur', function () {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            if (confirm && password !== confirm) {
                setFieldError?.(confirmInput, 'Les mots de passe ne correspondent pas.');
            }
        });
    }

    form.addEventListener('submit', function (e) {
        window.salangAuthClearJsFieldErrors?.(form);

        const name = nameInput?.value.trim() ?? '';
        const email = emailInput?.value.trim() ?? '';
        const sponsor = sponsorInput?.value.trim() ?? '';
        const password = passwordInput?.value ?? '';
        const confirm = confirmInput?.value ?? '';
        const terms = termsInput?.checked;

        let blocked = false;
        let firstFocus = null;

        function fail(input, msg) {
            blocked = true;
            setFieldError?.(input, msg);
            if (!firstFocus && input && typeof input.focus === 'function') {
                firstFocus = input;
            }
        }

        if (!name) {
            fail(nameInput, 'Nom complet requis.');
        } else {
            setFieldError?.(nameInput, null);
        }

        if (!email) {
            fail(emailInput, 'Adresse email requise.');
        } else if (!emailRe.test(email)) {
            fail(emailInput, 'Format d’email invalide.');
        } else if (!emailChecked) {
            fail(emailInput, 'Attendez la fin de la vérification de l’email.');
        } else if (emailInput?.classList.contains('auth-input-error')) {
            fail(emailInput, 'Cet email ne peut pas être utilisé.');
        } else {
            setFieldError?.(emailInput, null);
        }

        if (!sponsor) {
            fail(sponsorInput, 'Code parrain requis.');
        } else if (sponsor.length < 3) {
            fail(sponsorInput, 'Code parrain trop court.');
        } else if (sponsorInput?.classList.contains('auth-input-error')) {
            fail(sponsorInput, 'Code parrain invalide.');
        } else if (sponsorStatus?.classList.contains('loading')) {
            fail(sponsorInput, 'Vérification du parrain en cours…');
        } else {
            setFieldError?.(sponsorInput, null);
        }

        if (password.length < 8) {
            fail(passwordInput, 'Mot de passe : 8 caractères minimum.');
        } else {
            setFieldError?.(passwordInput, null);
        }

        if (password !== confirm) {
            fail(confirmInput, 'Les mots de passe ne correspondent pas.');
        } else if (confirm) {
            setFieldError?.(confirmInput, null);
        }

        if (!terms) {
            blocked = true;
            setFieldError?.(termsInput, 'Acceptez les conditions générales et la politique de confidentialité.');
        } else {
            setFieldError?.(termsInput, null);
        }

        if (blocked) {
            e.preventDefault();
            firstFocus?.focus();
        }
    });
})();
