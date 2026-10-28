<style>
    .mrd-viewer {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-height: calc(100dvh - 7rem);
    }
    .mrd-viewer__alert {
        border-radius: 0.75rem;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
        padding: 0.7rem 0.85rem;
        font-size: 0.85rem;
    }
    .mrd-viewer__stage {
        flex: 1;
        background: #5b6575;
        border-radius: 0.85rem;
        padding: 0.85rem 0.55rem 1rem;
        overflow: hidden;
    }
    .mrd-viewer__pages {
        position: relative;
        width: min(100%, 980px);
        margin: 0 auto;
    }
    .mrd-paper {
        display: none;
        background: #fff;
        color: #111;
        border-radius: 0.2rem;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
        padding: 0.7rem 0.65rem 0.55rem;
        min-height: 62dvh;
    }
    .mrd-paper.is-active { display: block; }
    .mrd-paper__header {
        display: grid;
        grid-template-columns: 42px 1fr 42px;
        gap: 0.25rem;
        align-items: center;
        border-bottom: 2.5px solid #8b0000;
        padding-bottom: 0.35rem;
        margin-bottom: 0.35rem;
    }
    .mrd-paper__header--compact {
        grid-template-columns: 32px 1fr;
    }
    .mrd-paper__logo {
        width: 40px;
        height: auto;
        object-fit: contain;
    }
    .mrd-paper__header--compact .mrd-paper__logo { width: 30px; }
    .mrd-paper__brand { text-align: center; }
    .mrd-paper__company {
        font-size: 0.78rem;
        font-weight: 800;
        color: #558b2f;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        line-height: 1.15;
    }
    .mrd-paper__ids,
    .mrd-paper__address {
        font-size: 0.5rem;
        line-height: 1.3;
        margin-top: 0.1rem;
    }
    .mrd-paper__address strong { color: #0E2F76; }
    .mrd-paper__title {
        text-align: center;
        font-size: 0.92rem;
        font-weight: 800;
        color: #0E2F76;
        text-transform: uppercase;
        text-decoration: underline;
        margin: 0.35rem 0 0.25rem;
    }
    .mrd-paper__meta {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 0.12rem 0.4rem;
        font-size: 0.62rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
    }
    .mrd-paper__meta > :nth-child(3),
    .mrd-paper__meta > :nth-child(6) { text-align: right; }
    .mrd-paper__period {
        grid-row: span 2;
        align-self: center;
        color: #8b0000;
        text-transform: uppercase;
        text-decoration: underline;
        font-size: 0.78rem;
        font-weight: 800;
        text-align: center;
        padding: 0 0.25rem;
    }
    .mrd-paper__meta-spacer { visibility: hidden; }
    .mrd-paper__section {
        font-size: 0.7rem;
        font-weight: 800;
        color: #558b2f;
        text-transform: uppercase;
        border-bottom: 2px solid #8b0000;
        padding-bottom: 0.1rem;
        margin: 0.45rem 0 0.3rem;
    }
    .mrd-paper table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .mrd-paper th,
    .mrd-paper td {
        border: 1px solid #000;
        padding: 0.18rem 0.22rem;
        font-size: 0.52rem;
        vertical-align: middle;
        word-wrap: break-word;
    }
    .mrd-paper__info th {
        background: #f2f2f2;
        font-weight: 700;
        width: 16%;
        text-align: left;
    }
    .mrd-paper__summary th {
        background: #f2f2f2;
        text-transform: uppercase;
        font-weight: 700;
        text-align: center;
    }
    .mrd-paper__summary td {
        text-align: center;
        font-weight: 800;
        font-size: 0.72rem;
        color: #0E2F76;
        background: #f9f9f9;
    }
    .mrd-paper__table th {
        background: #f2f2f2;
        text-transform: uppercase;
        font-weight: 800;
        text-align: center;
        font-size: 0.48rem;
    }
    .mrd-paper__table td {
        text-align: center;
        font-size: 0.5rem;
    }
    .mrd-paper__table .tl { text-align: left; }
    .mrd-paper__table .tr { text-align: right; }
    .mrd-paper__table tbody tr:nth-child(even) td { background: #fafafa; }
    .mrd-paper__empty {
        text-align: center;
        border: 1px solid #000;
        padding: 0.85rem;
        font-size: 0.72rem;
        color: #555;
        margin: 0;
    }
    .mrd-paper__footer {
        margin-top: 0.45rem;
        padding-top: 0.3rem;
        border-top: 1px solid #8b0000;
        text-align: center;
        font-size: 0.5rem;
        color: #444;
    }
    .mrd-paper__page-label {
        text-align: right;
        font-size: 0.55rem;
        color: #666;
        margin-top: 0.25rem;
        font-weight: 600;
    }
    .mrd-viewer__pager {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        margin-top: 0.65rem;
    }
    .mrd-viewer__pager-btn {
        min-width: 2.5rem;
        height: 2.35rem;
        border-radius: 0.6rem;
        border: 0;
        background: rgba(255,255,255,0.92);
        color: #0E2F76;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
    }
    .mrd-viewer__pager-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .mrd-viewer__pager-label {
        color: #fff;
        font-size: 0.85rem;
        font-weight: 700;
        min-width: 4.5rem;
        text-align: center;
    }
    .mrd-viewer__actions {
        display: flex;
        gap: 0.65rem;
        position: sticky;
        bottom: 0.35rem;
        z-index: 8;
    }
    .mrd-viewer__btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.75rem;
        border-radius: 0.75rem;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
    }
    .mrd-viewer__btn--ghost {
        border: 1.5px solid #0E2F76;
        color: #0E2F76;
        background: #fff;
    }
    .mrd-viewer__btn--primary {
        border: 1.5px solid var(--member-rank-accent, var(--dashboard-accent, #0E2F76));
        background: var(--member-rank-accent, var(--dashboard-accent, #0E2F76));
        color: #fff;
    }
    @media (min-width: 768px) {
        .mrd-viewer__stage { padding: 1.1rem 1.25rem 1.25rem; }
        .mrd-paper {
            padding: 1rem 1.1rem 0.75rem;
            min-height: 68dvh;
        }
        .mrd-paper__company { font-size: 1rem; }
        .mrd-paper__ids,
        .mrd-paper__address { font-size: 0.62rem; }
        .mrd-paper__title { font-size: 1.1rem; }
        .mrd-paper__meta { font-size: 0.75rem; }
        .mrd-paper__period { font-size: 0.95rem; }
        .mrd-paper__section { font-size: 0.82rem; }
        .mrd-paper th,
        .mrd-paper td { font-size: 0.68rem; padding: 0.28rem 0.35rem; }
        .mrd-paper__table th { font-size: 0.62rem; }
        .mrd-paper__table td { font-size: 0.68rem; }
        .mrd-paper__summary td { font-size: 0.9rem; }
    }
</style>
