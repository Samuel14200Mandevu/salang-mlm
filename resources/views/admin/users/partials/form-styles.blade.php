<style>
.user-form-layout {
    display: grid;
    gap: 1rem;
}
@media (min-width: 1024px) {
    .user-form-layout {
        grid-template-columns: minmax(0, 1fr) 20rem;
        align-items: start;
        gap: 1.25rem;
    }
}
@media (min-width: 1280px) {
    .user-form-layout {
        grid-template-columns: minmax(0, 1fr) 22rem;
    }
}

.user-form-section {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 0.85rem;
    padding: 1rem 1.1rem 1.15rem;
}
.user-form-section__head {
    margin-bottom: 0.9rem;
    padding-bottom: 0.7rem;
    border-bottom: 1px solid var(--border-light);
}
.user-form-section__head h3 {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--text-primary);
}
.user-form-section__head p {
    margin: 0.2rem 0 0;
    font-size: 0.72rem;
    color: var(--text-tertiary);
}

.user-form-grid {
    display: grid;
    gap: 0.85rem 1rem;
}
.user-form-grid--2 { grid-template-columns: 1fr; }
@media (min-width: 640px) {
    .user-form-grid--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
.user-form-grid__full { grid-column: 1 / -1; }

.form-group { margin: 0; }
.form-group label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 0.35rem;
}
.form-group .required { color: var(--danger); }
.form-group .help-text,
.help-text {
    display: block;
    font-size: 0.68rem;
    color: var(--text-tertiary);
    margin-top: 0.3rem;
}
.field-error {
    margin: 0.3rem 0 0;
    font-size: 0.7rem;
    color: var(--danger);
}
.form-control {
    width: 100%;
    padding: 0.55rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 0.55rem;
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.875rem;
    outline: none;
    transition: border-color 0.15s ease;
}
.form-control:focus { border-color: var(--primary); }
.form-control-error { border-color: var(--danger); }
textarea.form-control { resize: vertical; min-height: 4.5rem; }

.info-box,
.warning-box {
    border-radius: 0.65rem;
    padding: 0.75rem 0.85rem;
    font-size: 0.8rem;
}
.info-box {
    background: color-mix(in srgb, var(--primary) 8%, transparent);
    border: 1px solid color-mix(in srgb, var(--primary) 18%, transparent);
    color: var(--text-secondary);
}
.warning-box {
    background: rgba(217, 119, 6, 0.08);
    border: 1px solid rgba(217, 119, 6, 0.2);
    color: #92400E;
}
.current-sponsor-box {
    margin-top: 0.45rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 0.5rem 0.7rem;
    font-size: 0.72rem;
    color: var(--text-secondary);
}
.current-sponsor-box strong { color: var(--text-primary); }

.parrain-feedback {
    font-size: 0.72rem;
    margin-top: 0.3rem;
    min-height: 1rem;
}
.parrain-feedback.success { color: #1C7E4A; }
.parrain-feedback.error { color: #B91C1C; }
.parrain-feedback.loading { color: var(--text-tertiary); }

.user-form-actions {
    display: grid;
    gap: 0.5rem;
}
.user-form-side {
    position: relative;
}
@media (min-width: 1024px) {
    .user-form-side {
        position: sticky;
        top: 1rem;
    }
}

.user-avatar-preview {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 999px;
    object-fit: cover;
    border: 2px solid var(--primary);
}
.user-avatar-placeholder {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary);
    color: white;
    font-size: 1.35rem;
    font-weight: 700;
}

@media (max-width: 640px) {
    .admin-mobile-page--user-form {
        padding-bottom: 5.5rem;
    }
    .user-form-actions--mobile-fixed {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 40;
        display: flex;
        gap: 0.5rem;
        padding: 0.65rem 0.75rem calc(0.65rem + env(safe-area-inset-bottom, 0px));
        background: color-mix(in srgb, var(--bg-page) 94%, transparent);
        backdrop-filter: blur(10px);
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.08);
    }
    .user-form-actions--mobile-fixed .btn {
        flex: 1 1 auto;
        min-height: 2.75rem;
    }
    .user-form-side .user-form-actions {
        display: none;
    }
}
@media (min-width: 641px) {
    .user-form-actions--mobile-fixed { display: none; }
}
</style>
