function formatUsd(value) {
    const n = Number.isFinite(value) ? value : 0;
    return '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function initWithdrawForm(form) {
    if (!form || form.dataset.withdrawInit === '1') {
        return;
    }
    form.dataset.withdrawInit = '1';

    const balance = parseFloat(form.dataset.balance || '0') || 0;
    const feeRate = parseFloat(form.dataset.feeRate || '0.025') || 0.025;
    const minAmount = parseFloat(form.dataset.min || '10') || 10;
    const requireDest = form.dataset.requireDest !== '0';
    const maxAmount = form.dataset.max
        ? parseFloat(form.dataset.max) || 0
        : balance;
    const isDeposit = form.classList.contains('member-deposit-form');
    const defaultSubmitLabel = isDeposit ? 'Confirmer le dépôt' : 'Confirmer le retrait';

    const methodSelect = form.querySelector('[data-withdraw-method-select]');
    const methodButtons = form.querySelectorAll('[data-withdraw-method]');
    const amountInput = form.querySelector('[data-withdraw-amount]');
    const maxBtn = form.querySelector('[data-withdraw-max]');
    const submitBtn = form.querySelector('[data-withdraw-submit]');

    const grossEl = form.querySelector('[data-withdraw-summary-gross]');
    const feeEl = form.querySelector('[data-withdraw-summary-fee]');
    const netEl = form.querySelector('[data-withdraw-summary-net]');

    const fieldGroups = form.querySelectorAll('[data-withdraw-fields]');

    function activeMethod() {
        return methodSelect ? methodSelect.value : 'crypto';
    }

    function visibleDestInput() {
        const method = activeMethod();
        const group = form.querySelector('[data-withdraw-fields="' + method + '"]');
        if (!group) {
            return null;
        }
        return group.querySelector('input, textarea');
    }

    function syncFieldRequirements() {
        fieldGroups.forEach(function (group) {
            const method = group.getAttribute('data-withdraw-fields');
            const isActive = method === activeMethod();
            group.hidden = !isActive;
            group.classList.toggle('hidden', !isActive);
            group.querySelectorAll('input, textarea').forEach(function (el) {
                el.required = isActive && requireDest;
                if (!isActive) {
                    el.removeAttribute('aria-invalid');
                }
            });
        });
    }

    function selectMethod(method, button) {
        if (methodSelect) {
            methodSelect.value = method;
        }
        methodButtons.forEach(function (btn) {
            btn.classList.toggle('is-selected', btn === button);
        });
        syncFieldRequirements();
        updateSummary();
    }

    function parseAmount() {
        if (!amountInput) {
            return 0;
        }
        const raw = parseFloat(amountInput.value);
        return Number.isFinite(raw) ? raw : 0;
    }

    function updateSummary() {
        const amount = parseAmount();
        const fee = amount > 0 ? amount * feeRate : 0;
        const net = Math.max(0, amount - fee);

        if (grossEl) {
            grossEl.textContent = formatUsd(amount);
        }
        if (feeEl) {
            feeEl.textContent = formatUsd(fee);
        }
        if (netEl) {
            netEl.textContent = formatUsd(net);
        }

        const dest = visibleDestInput();
        const destOk = !requireDest || (dest ? String(dest.value || '').trim().length > 0 : false);
        let amountOk = amount >= minAmount && amount <= maxAmount;
        if (!isDeposit && balance <= 0) {
            amountOk = false;
        }

        if (submitBtn) {
            submitBtn.disabled = !(amountOk && destOk);
            if (amountOk && destOk) {
                submitBtn.textContent = 'Confirmer ' + formatUsd(amount);
            } else {
                submitBtn.textContent = defaultSubmitLabel;
            }
        }
    }

    methodButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            selectMethod(btn.dataset.method, btn);
        });
    });

    if (amountInput) {
        amountInput.addEventListener('input', updateSummary);
        amountInput.addEventListener('change', updateSummary);
    }

    if (maxBtn && amountInput) {
        maxBtn.addEventListener('click', function () {
            const maxVal = balance >= minAmount ? balance : 0;
            amountInput.value = maxVal > 0 ? maxVal.toFixed(2) : '';
            updateSummary();
        });
    }

    fieldGroups.forEach(function (group) {
        group.querySelectorAll('input, textarea').forEach(function (el) {
            el.addEventListener('input', updateSummary);
        });
    });

    const preselected = form.querySelector('.member-withdraw-method.is-selected');
    if (preselected) {
        selectMethod(preselected.dataset.method, preselected);
    } else {
        syncFieldRequirements();
        updateSummary();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-withdraw-form]').forEach(initWithdrawForm);
});

export { initWithdrawForm };
