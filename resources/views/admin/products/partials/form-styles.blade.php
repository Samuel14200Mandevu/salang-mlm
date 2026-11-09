<style>
.product-form-layout {
    display: grid;
    gap: 1rem;
}
@media (min-width: 1024px) {
    .product-form-layout {
        grid-template-columns: minmax(0, 1fr) 20rem;
        align-items: start;
        gap: 1.25rem;
    }
}
@media (min-width: 1280px) {
    .product-form-layout {
        grid-template-columns: minmax(0, 1fr) 22rem;
    }
}

.product-form-section {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 0.85rem;
    padding: 1rem 1.1rem 1.15rem;
}
.product-form-section__head {
    margin-bottom: 0.9rem;
    padding-bottom: 0.7rem;
    border-bottom: 1px solid var(--border-light);
}
.product-form-section__head h3 {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--text-primary);
}
.product-form-section__head p {
    margin: 0.2rem 0 0;
    font-size: 0.72rem;
    color: var(--text-tertiary);
}

.product-form-grid {
    display: grid;
    gap: 0.85rem 1rem;
}
.product-form-grid--2 { grid-template-columns: 1fr; }
.product-form-grid--3 { grid-template-columns: 1fr; }
.product-form-grid--4 { grid-template-columns: 1fr; }
@media (min-width: 640px) {
    .product-form-grid--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .product-form-grid--3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .product-form-grid--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (min-width: 900px) {
    .product-form-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .product-form-grid--4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}
.product-form-grid__full { grid-column: 1 / -1; }
@media (min-width: 640px) {
    .product-form-grid__span-2 { grid-column: span 2; }
}

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
textarea.form-control { resize: vertical; min-height: 6rem; }

.image-dropzone {
    width: 100%;
    aspect-ratio: 1 / 1;
    max-height: 16rem;
    border: 1.5px dashed var(--border-color);
    border-radius: 0.75rem;
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.image-dropzone:hover {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary) 4%, var(--bg-secondary));
}
.image-dropzone img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.image-dropzone__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.45rem;
    color: var(--text-tertiary);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 1rem;
    text-align: center;
}

.product-form-toggles {
    display: grid;
    gap: 0.55rem;
}
.toggle-row {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.65rem 0.7rem;
    border: 1px solid var(--border-color);
    border-radius: 0.65rem;
    background: var(--bg-secondary);
    cursor: pointer;
}
.toggle-row input {
    margin-top: 0.15rem;
}
.toggle-row strong {
    display: block;
    font-size: 0.8rem;
    color: var(--text-primary);
}
.toggle-row small {
    display: block;
    margin-top: 0.1rem;
    font-size: 0.68rem;
    color: var(--text-tertiary);
}

.product-form-actions {
    display: grid;
    gap: 0.5rem;
    position: sticky;
    bottom: 0.75rem;
}
.product-form-side {
    position: relative;
}
@media (min-width: 1024px) {
    .product-form-side {
        position: sticky;
        top: 1rem;
    }
    .product-form-actions {
        position: static;
    }
}
</style>
